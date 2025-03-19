<!-- Notificações -->
@if(session('success'))
    <div class="pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p style="color: green;">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p style="color: darkred;">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    </div>
@endif
