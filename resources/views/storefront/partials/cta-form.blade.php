<section id="contacts" class="border-b border-brand-black/10 bg-white py-16 lg:py-28">
    <span id="apply" class="sr-only"></span>

    <div class="mx-auto grid max-w-[1324px] grid-cols-1 gap-10 px-5 sm:px-8 lg:grid-cols-[minmax(0,0.9fr)_minmax(420px,1fr)] lg:gap-16 lg:px-0">
        <div class="flex flex-col justify-between">
            <div>
                <x-storefront.section-label number="07">{{ $homeContent->get('cta_section.eyebrow') }}</x-storefront.section-label>

                <h2 class="mt-6 max-w-[620px] text-[42px] leading-[0.98] font-normal sm:text-[64px] lg:text-[82px]">
                    {{ $homeContent->get('cta_section.heading_main') }}
                    <span class="text-brand-pink">{{ $homeContent->get('cta_section.heading_accent') }}</span>
                </h2>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-brand-black/65">{{ $homeContent->get('cta_section.subcopy') }}</p>
            </div>

            <dl class="mt-10 divide-y divide-brand-black/10 border-y border-brand-black/10 text-sm">
                <div class="grid gap-1 py-4 sm:grid-cols-[120px_1fr]">
                    <dt class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">{{ $homeContent->get('cta_section.email_label') }}</dt>
                    <dd class="font-bold">{{ $homeContent->get('cta_section.email') }}</dd>
                </div>
                <div class="grid gap-1 py-4 sm:grid-cols-[120px_1fr]">
                    <dt class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">{{ $homeContent->get('cta_section.address_label') }}</dt>
                    <dd class="font-bold">{{ $homeContent->get('cta_section.address') }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-brand-cream p-6 sm:p-8 lg:p-10">
            @if (session('orderSubmitted'))
                <p class="text-2xl font-normal text-brand-black">{{ $homeContent->get('form.success') }}</p>
            @else
                <form method="POST" action="{{ route('orders.store') }}#contacts" class="grid gap-6 sm:grid-cols-2">
                    @csrf
                    <template x-for="(line, index) in $store.orderBuilder.lines" :key="line.product_id">
                        <div>
                            <input type="hidden" :name="`order_lines[${index}][product_id]`" :value="line.product_id">
                            <input type="hidden" :name="`order_lines[${index}][quantity]`" :value="line.quantity">
                            <input type="hidden" :name="`order_lines[${index}][density]`" :value="line.density">
                            <input type="hidden" :name="`order_lines[${index}][size]`" :value="line.size">
                        </div>
                    </template>

                    <div>
                        <label for="company" class="text-xs font-bold tracking-wide text-brand-black/50 uppercase">{{ $homeContent->get('form.company') }}</label>
                        <input
                            type="text"
                            id="company"
                            name="company"
                            value="{{ old('company') }}"
                            placeholder="Название компании или ИП"
                            class="mt-1 w-full border-0 border-b border-brand-black/20 bg-transparent px-0 py-2 text-sm placeholder:text-brand-black/30 focus:border-brand-pink focus:ring-0"
                        >
                        @error('company')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_name" class="text-xs font-bold tracking-wide text-brand-black/50 uppercase">{{ $homeContent->get('form.contact_person') }}</label>
                        <input
                            type="text"
                            id="customer_name"
                            name="customer_name"
                            value="{{ old('customer_name') }}"
                            placeholder="Как к вам обращаться"
                            required
                            class="mt-1 w-full border-0 border-b border-brand-black/20 bg-transparent px-0 py-2 text-sm placeholder:text-brand-black/30 focus:border-brand-pink focus:ring-0"
                        >
                        @error('customer_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="text-xs font-bold tracking-wide text-brand-black/50 uppercase">{{ $homeContent->get('form.phone') }}</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone') }}"
                            placeholder="+7 (___) ___-__-__"
                            required
                            class="mt-1 w-full border-0 border-b border-brand-black/20 bg-transparent px-0 py-2 text-sm placeholder:text-brand-black/30 focus:border-brand-pink focus:ring-0"
                        >
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="volume" class="text-xs font-bold tracking-wide text-brand-black/50 uppercase">{{ $homeContent->get('form.volume') }}</label>
                        <select
                            id="volume"
                            name="volume"
                            x-model="$store.volume.selected"
                            class="mt-1 w-full border-0 border-b border-brand-black/20 bg-transparent px-0 py-2 text-sm focus:border-brand-pink focus:ring-0"
                        >
                            @foreach ($homeContent->get('form.volume_options', []) as $option)
                                <option value="{{ $option['key'] }}" {{ old('volume') === $option['key'] ? 'selected' : '' }}>
                                    {{ $option['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('volume')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="message" class="text-xs font-bold tracking-wide text-brand-black/50 uppercase">{{ $homeContent->get('form.comment') }}</label>
                        <textarea
                            id="message"
                            name="message"
                            rows="2"
                            placeholder="Комментарий к заказу, упаковке, маркировке или срокам"
                            class="mt-1 w-full border-0 border-b border-brand-black/20 bg-transparent px-0 py-2 text-sm placeholder:text-brand-black/30 focus:border-brand-pink focus:ring-0"
                        >{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="flex w-full items-center justify-between bg-brand-pink px-6 py-4 text-sm font-bold text-white sm:col-span-2">
                        {{ $homeContent->get('form.submit') }}
                        <span aria-hidden="true">&#8599;&#65038;</span>
                    </button>

                    <p class="text-xs text-brand-black/50 sm:col-span-2">{{ $homeContent->get('form.helper') }}</p>

                    @php
                        $lineError = collect($errors->getMessages())->first(
                            fn (array $messages, string $key): bool => str_starts_with($key, 'order_lines')
                        );
                    @endphp
                    @if ($lineError)
                        <p class="text-xs text-red-600 sm:col-span-2">{{ $lineError[0] }}</p>
                    @endif
                </form>
            @endif
        </div>
    </div>
</section>
