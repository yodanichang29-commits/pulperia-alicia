<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-400 to-purple-500 border-2 border-purple-300 rounded-xl font-bold text-base text-white shadow-lg hover:from-purple-500 hover:to-purple-600 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-purple-300 active:scale-95 transition-all duration-200']) }}>
    {{ $slot }}
</button>
