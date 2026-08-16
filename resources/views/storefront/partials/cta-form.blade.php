<section id="apply" class="border-b border-brand-black/10 bg-white py-16 lg:py-24">
    <div class="grid grid-cols-1 gap-10 px-6 lg:grid-cols-2 lg:px-16">
        <div>
            <x-storefront.section-label number="07">{{ $homeContent->get('cta_section.eyebrow') }}</x-storefront.section-label>

            <h2 class="mt-6 text-3xl font-extrabold sm:text-4xl">
                {{ $homeContent->get('cta_section.heading_main') }}<br>
                <span class="text-brand-pink">{{ $homeContent->get('cta_section.heading_accent') }}</span>
            </h2>
            <p class="mt-4 max-w-md text-sm text-brand-black/60">{{ $homeContent->get('cta_section.subcopy') }}</p>

            <dl class="mt-8 space-y-2 text-sm">
                <div class="flex gap-2">
                    <dt class="text-brand-black/50">{{ $homeContent->get('cta_section.email_label') }}</dt>
                    <dd>{{ $homeContent->get('cta_section.email') }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="text-brand-black/50">{{ $homeContent->get('cta_section.address_label') }}</dt>
                    <dd>{{ $homeContent->get('cta_section.address') }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl bg-brand-cream p-6 sm:p-8">
            @if (session('applicationSubmitted'))
                <p class="text-lg font-bold text-brand-black">{{ $homeContent->get('form.success') }}</p>
            @else
                <form x-data="applicationForm" method="POST" action="{{ route('applications.store') }}#apply" class="space-y-5">
                    @csrf

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
                            x-model="volume"
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

                    <div>
                        <label for="message" class="text-xs font-bold tracking-wide text-brand-black/50 uppercase">{{ $homeContent->get('form.comment') }}</label>
                        <textarea
                            id="message"
                            name="message"
                            rows="2"
                            placeholder="Модель, цвет, плотность, размеры, нанесение и упаковка"
                            class="mt-1 w-full border-0 border-b border-brand-black/20 bg-transparent px-0 py-2 text-sm placeholder:text-brand-black/30 focus:border-brand-pink focus:ring-0"
                        >{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="flex w-full items-center justify-between bg-brand-pink px-6 py-3 text-sm font-bold text-white">
                        {{ $homeContent->get('form.submit') }}
                        <span aria-hidden="true">↗</span>
                    </button>

                    <p class="text-xs text-brand-black/50">{{ $homeContent->get('form.helper') }}</p>
                </form>
            @endif
        </div>
    </div>
</section>
