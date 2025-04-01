<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-indigo-800">Student Dashboard</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700">Welcome, <span id="student-name" class="font-semibold"></span></span>
                <button id="logout-btn" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Current Enrollments -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-indigo-700">
                    <i class="fas fa-book-open mr-2"></i>Current Enrollments
                </h2>
                <div id="enrollments-list" class="space-y-4">
                    <!-- Enrollment cards will be loaded here via JavaScript -->
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                        <p>Loading enrollments...</p>
                    </div>
                </div>
            </div>

            <!-- Available Subjects -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-indigo-700">
                    <i class="fas fa-search mr-2"></i>Available Subjects
                </h2>
                <div class="mb-4">
                    <input type="text" id="subject-search" placeholder="Search subjects..." 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div id="subjects-list" class="space-y-4">
                    <!-- Subject cards will be loaded here via JavaScript -->
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                        <p>Loading available subjects...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment Modal -->
    <div id="enroll-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-xl font-semibold mb-4">Enroll in Subject</h3>
            <p id="modal-subject-name" class="mb-4"></p>
            <div class="flex justify-end space-x-4">
                <button id="cancel-enroll" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button id="confirm-enroll" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Confirm Enrollment
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentStudentId = null;
        let selectedSubjectId = null;

        // DOM Content Loaded
        document.addEventListener('DOMContentLoaded', async () => {
            // Get student info from session/token
            await fetchStudentInfo();
            
            // Load enrollments and subjects
            loadEnrollments();
            loadSubjects();

            // Set up event listeners
            document.getElementById('logout-btn').addEventListener('click', logout);
            document.getElementById('subject-search').addEventListener('input', filterSubjects);
            document.getElementById('cancel-enroll').addEventListener('click', hideModal);
            document.getElementById('confirm-enroll').addEventListener('click', submitEnrollment);
        });

        async function fetchStudentInfo() {
            try {
                // In a real app, this would come from your auth system
                const response = await fetch('/api/auth/me');
                const data = await response.json();
                
                if (response.ok) {
                    currentStudentId = data.user_id;
                    document.getElementById('student-name').textContent = data.name;
                } else {
                    throw new Error(data.error || 'Failed to fetch student info');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to load student information');
            }
        }

        async function loadEnrollments() {
            try {
                const response = await fetch(`/api/enrollments?student_id=${currentStudentId}`);
                const enrollments = await response.json();
                
                const container = document.getElementById('enrollments-list');
                container.innerHTML = '';

                if (enrollments.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-book mr-2"></i>
                            <p>No enrollments found</p>
                        </div>
                    `;
                    return;
                }

                enrollments.forEach(enrollment => {
                    const statusColor = {
                        'Approved': 'bg-green-100 text-green-800',
                        'Pending': 'bg-yellow-100 text-yellow-800',
                        'Rejected': 'bg-red-100 text-red-800',
                        'Dropped': 'bg-gray-100 text-gray-800'
                    }[enrollment.enrollment_status];

                    container.innerHTML += `
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold">${enrollment.subject_name} (${enrollment.subject_code})</h3>
                                    <p class="text-sm text-gray-600">${enrollment.enrollment_type} Enrollment</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full ${statusColor}">
                                    ${enrollment.enrollment_status}
                                </span>
                            </div>
                            <div class="mt-2 text-sm">
                                <p>Enrolled: ${new Date(enrollment.enrolled_at).toLocaleDateString()}</p>
                            </div>
                        </div>
                    `;
                });
            } catch (error) {
                console.error('Error loading enrollments:', error);
                document.getElementById('enrollments-list').innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <p>Failed to load enrollments</p>
                    </div>
                `;
            }
        }

        async function loadSubjects() {
            try {
                const response = await fetch('/api/subjects');
                const subjects = await response.json();
                
                const container = document.getElementById('subjects-list');
                container.innerHTML = '';

                if (subjects.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-book mr-2"></i>
                            <p>No subjects available</p>
                        </div>
                    `;
                    return;
                }

                subjects.forEach(subject => {
                    container.innerHTML += `
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow" data-subject-id="${subject.subject_id}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold">${subject.name} (${subject.code})</h3>
                                    <p class="text-sm text-gray-600">Year ${subject.year_level}, ${subject.semester} Semester</p>
                                </div>
                                <span class="text-sm text-gray-500">${subject.credits} credits</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-700">${subject.description || 'No description available'}</p>
                            <button class="mt-3 enroll-btn px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                Enroll
                            </button>
                        </div>
                    `;
                });

                // Add event listeners to all enroll buttons
                document.querySelectorAll('.enroll-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const subjectCard = e.target.closest('[data-subject-id]');
                        selectedSubjectId = subjectCard.getAttribute('data-subject-id');
                        const subjectName = subjectCard.querySelector('h3').textContent;
                        
                        document.getElementById('modal-subject-name').textContent = `Are you sure you want to enroll in ${subjectName}?`;
                        document.getElementById('enroll-modal').classList.remove('hidden');
                    });
                });
            } catch (error) {
                console.error('Error loading subjects:', error);
                document.getElementById('subjects-list').innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <p>Failed to load subjects</p>
                    </div>
                `;
            }
        }

        function filterSubjects() {
            const searchTerm = document.getElementById('subject-search').value.toLowerCase();
            const subjectCards = document.querySelectorAll('[data-subject-id]');
            
            subjectCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        }

        async function submitEnrollment() {
            try {
                const response = await fetch('/api/enrollments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        subject_id: selectedSubjectId
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Enrollment request submitted successfully!');
                    hideModal();
                    loadEnrollments(); // Refresh the enrollments list
                } else {
                    throw new Error(result.error || 'Failed to submit enrollment');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to submit enrollment: ' + error.message);
            }
        }

        function hideModal() {
            document.getElementById('enroll-modal').classList.add('hidden');
            selectedSubjectId = null;
        }

        function logout() {
            // In a real app, this would clear the auth token
            alert('Logging out...');
            window.location.href = '/login';
        }
    </script>
</body>
</html>