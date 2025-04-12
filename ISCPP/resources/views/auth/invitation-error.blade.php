<x-layouts.auth.simple>
    <div class="mb-4 text-sm text-red-600 dark:text-red-400">
        <p class="font-medium text-lg">Invitation Error</p>
        <p class="mt-2">{{ $error }}</p>
    </div>

    <div class="flex items-center justify-center mt-4">
        <a href="{{ route('login') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
            Return to Login
        </a>
    </div>
</x-layouts.auth.simple>
