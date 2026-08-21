@php
    $submissionToken = old('submission_token') ?: session()->remember('order_submission_token', fn (): string => (string) \Illuminate\Support\Str::uuid());
    $oldOrderLines = collect(old('order_lines', []))->filter(fn ($line): bool => is_array($line) && filled($line['product_id'] ?? null));
    $oldProducts = $oldOrderLines->isEmpty()
        ? collect()
        : \App\Models\Catalog\Product::query()
            ->with('category')
            ->whereIn('id', $oldOrderLines->pluck('product_id')->all())
            ->get()
            ->keyBy('id');
    $oldLinesForStore = $oldOrderLines
        ->map(function (array $line) use ($oldProducts): ?array {
            $product = $oldProducts->get((int) ($line['product_id'] ?? 0));

            if (! $product) {
                return null;
            }

            return [
                'product_id' => $product->id,
                'name' => $product->name,
                'category' => $product->category?->name,
                'availability' => $product->isInStock() ? 'На складе' : 'Под заказ',
                'image' => $product->cover_image ?: asset('brand/catalog-white-v2.jpg'),
                'moq' => $product->moq,
                'quantity' => (int) ($line['quantity'] ?? $product->moq),
                'density' => $line['density'] ?? null,
                'size' => $line['size'] ?? null,
                'color' => $line['color'] ?? null,
            ];
        })
        ->filter()
        ->values();
@endphp

<section id="contacts" class="border-b border-brand-black/10 bg-white py-16 lg:py-28">
    <span id="apply" class="sr-only"></span>

    <div class="storefront-shell grid grid-cols-1 gap-10 lg:grid-cols-[minmax(0,0.9fr)_minmax(420px,1fr)] lg:gap-16">
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
                    <dd class="font-bold">
                        <a href="mailto:{{ $homeContent->get('cta_section.email') }}" @click="storefrontAnalytics.track('contact_click', { type: 'email', location: 'cta' })">
                            {{ $homeContent->get('cta_section.email') }}
                        </a>
                    </dd>
                </div>
                <div class="grid gap-1 py-4 sm:grid-cols-[120px_1fr]">
                    <dt class="text-xs font-bold tracking-wide text-brand-black/40 uppercase">{{ $homeContent->get('cta_section.address_label') }}</dt>
                    <dd class="font-bold">{{ $homeContent->get('cta_section.address') }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-brand-cream p-6 sm:p-8 lg:p-10">
            @if (session('orderSubmitted'))
                <div x-data x-init="storefrontAnalytics.track('form_success', { request_number: @js(session('orderRequestNumber')) })">
                    <p class="text-2xl font-normal text-brand-black">{{ $homeContent->get('form.success') }}</p>
                    @if (session('orderRequestNumber'))
                        <p class="mt-4 text-sm font-bold text-brand-black/60">
                            {{ $homeContent->get('form.success_number_label') }}
                            <span class="text-brand-black">{{ session('orderRequestNumber') }}</span>
                        </p>
                    @endif
                </div>
            @else
                @if ($errors->any())
                    <span x-data x-init="storefrontAnalytics.track('form_error', { fields: @js(array_keys($errors->getMessages())) })"></span>
                @endif

                <form method="POST" action="{{ route('orders.store') }}#contacts" class="grid gap-6 sm:grid-cols-2" x-data='requestForm(@json($oldLinesForStore, JSON_UNESCAPED_UNICODE))' @submit="submit($event)" @focusin="start()">
                    @csrf
                    <input type="hidden" name="submission_token" value="{{ $submissionToken }}">
                    <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                    <input type="hidden" name="landing_url" :value="$store.attribution.landing_url">
                    <input type="hidden" name="source_url" :value="$store.attribution.source_url">
                    <input type="hidden" name="referrer_url" :value="$store.attribution.referrer_url">
                    <input type="hidden" name="utm_source" :value="$store.attribution.utm_source">
                    <input type="hidden" name="utm_medium" :value="$store.attribution.utm_medium">
                    <input type="hidden" name="utm_campaign" :value="$store.attribution.utm_campaign">
                    <input type="hidden" name="utm_content" :value="$store.attribution.utm_content">
                    <input type="hidden" name="utm_term" :value="$store.attribution.utm_term">

                    <template x-for="(line, index) in $store.orderBuilder.lines" :key="line.product_id">
                        <div style="position: absolute;">
                            <input type="hidden" :name="`order_lines[${index}][product_id]`" :value="line.product_id">
                            <input type="hidden" :name="`order_lines[${index}][quantity]`" :value="line.quantity">
                            <input type="hidden" :name="`order_lines[${index}][density]`" :value="line.density">
                            <input type="hidden" :name="`order_lines[${index}][size]`" :value="line.size">
                            <input type="hidden" :name="`order_lines[${index}][color]`" :value="line.color">
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
                        <label for="email" class="text-xs font-bold tracking-wide text-brand-black/50 uppercase">{{ $homeContent->get('form.email') }}</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="name@company.ru"
                            required
                            class="mt-1 w-full border-0 border-b border-brand-black/20 bg-transparent px-0 py-2 text-sm placeholder:text-brand-black/30 focus:border-brand-pink focus:ring-0"
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="preferred_contact_method" class="text-xs font-bold tracking-wide text-brand-black/50 uppercase">{{ $homeContent->get('form.preferred_contact_method') }}</label>
                        <select
                            id="preferred_contact_method"
                            name="preferred_contact_method"
                            required
                            class="mt-1 w-full border-0 border-b border-brand-black/20 bg-transparent px-0 py-2 text-sm focus:border-brand-pink focus:ring-0"
                        >
                            <option value="phone" @selected(old('preferred_contact_method', 'phone') === 'phone')>{{ $homeContent->get('form.contact_phone') }}</option>
                            <option value="email" @selected(old('preferred_contact_method') === 'email')>{{ $homeContent->get('form.contact_email') }}</option>
                        </select>
                        @error('preferred_contact_method')
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

                    <label class="flex items-start gap-3 text-xs leading-relaxed text-brand-black/60 sm:col-span-2">
                        <input type="checkbox" name="consent" value="1" @checked(old('consent')) required class="mt-0.5 border-brand-black/20 text-brand-pink focus:ring-brand-pink">
                        <span>
                            {{ $homeContent->get('form.consent') }}:
                            <a href="{{ route('legal.privacy') }}" class="font-bold text-brand-black hover:text-brand-pink">{{ $homeContent->get('form.privacy_link') }}</a>
                            /
                            <a href="{{ route('legal.consent') }}" class="font-bold text-brand-black hover:text-brand-pink">{{ $homeContent->get('form.consent_link') }}</a>
                        </span>
                    </label>
                    @error('consent')
                        <p class="text-xs text-red-600 sm:col-span-2">{{ $message }}</p>
                    @enderror

                    <button type="submit" :disabled="submitting" class="flex w-full items-center justify-between bg-brand-pink px-6 py-4 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-60 sm:col-span-2">
                        <span x-text="submitting ? 'Отправляем...' : @js($homeContent->get('form.submit'))">{{ $homeContent->get('form.submit') }}</span>
                        <span aria-hidden="true">&#8599;&#65038;</span>
                    </button>

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
