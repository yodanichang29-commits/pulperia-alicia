@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'px-4 py-3 text-base border-2 border-gray-300 focus:border-purple-400 focus:ring-4 focus:ring-purple-200 rounded-xl shadow-sm disabled:bg-gray-100 disabled:opacity-50 transition-all duration-200']) }}>
