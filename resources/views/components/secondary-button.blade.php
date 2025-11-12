<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center gap-2 px-6 py-3 bg-white border-2 border-gray-300 rounded-xl font-semibold text-base text-gray-700 shadow-md hover:bg-gray-50 hover:border-gray-400 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-gray-200 disabled:opacity-50 active:scale-95 transition-all duration-200']) }}>
    {{ $slot }}
</button>
