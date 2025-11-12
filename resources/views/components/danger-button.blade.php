<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-rose-300 to-pink-300 border-2 border-rose-400 rounded-xl font-bold text-base text-gray-800 shadow-lg hover:from-rose-400 hover:to-pink-400 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-rose-200 active:scale-95 transition-all duration-200']) }}>
    {{ $slot }}
</button>
