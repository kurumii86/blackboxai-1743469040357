<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | LMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .error-bg {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
    </style>
</head>
<body class="error-bg min-h-screen flex items-center">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center">
            <div class="inline-flex items-center justify-center mb-6">
                <div class="w-24 h-24 rounded-full bg-white shadow-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-yellow-500"></i>
                </div>
            </div>
            <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
            <h2 class="text-3xl font-semibold text-gray-700 mb-6">Oops! Page Not Found</h2>
            <p class="text-xl text-gray-600 mb-8">
                The page you're looking for might have been removed, had its name changed, or is temporarily unavailable.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/" class="px-6 py-3 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition-colors flex items-center">
                    <i class="fas fa-home mr-2"></i> Return Home
                </a>
                <a href="/dashboard" class="px-6 py-3 bg-white text-indigo-600 border border-indigo-600 rounded-lg shadow hover:bg-indigo-50 transition-colors flex items-center">
                    <i class="fas fa-tachometer-alt mr-2"></i> Go to Dashboard
                </a>
                <a href="mailto:support@lms.edu" class="px-6 py-3 bg-white text-gray-600 border border-gray-300 rounded-lg shadow hover:bg-gray-50 transition-colors flex items-center">
                    <i class="fas fa-envelope mr-2"></i> Contact Support
                </a>
            </div>
        </div>
    </div>
</body>
</html>