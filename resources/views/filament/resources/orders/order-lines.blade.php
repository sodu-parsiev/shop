@php
    use App\Filament\Resources\Catalog\Products\ProductResource;

    $lines = $record?->lines()->with('product')->get() ?? collect();
@endphp

<div style="display: flex; flex-direction: column; gap: 0.75rem;">
    @forelse ($lines as $line)
        @php
            $product = $line->product;
            $coverImage = $product?->cover_image;
            $coverImage = $coverImage
                ? (str_starts_with($coverImage, 'http') ? $coverImage : asset(ltrim($coverImage, '/')))
                : null;
            $attributes = collect([$line->preferred_color, $line->preferred_density, $line->preferred_size])
                ->filter()
                ->flatMap(fn (string $value): array => array_map('trim', explode(',', $value)))
                ->filter();
        @endphp

        <div style="display: flex; align-items: center; gap: 0.875rem; padding: 0.75rem; border: 1px solid rgba(120, 120, 120, 0.2); border-radius: 0.5rem;">
            @if ($coverImage)
                <x-filament::avatar :src="$coverImage" :alt="$line->product_name" size="lg" :circular="false" />
            @else
                <div style="width: 3rem; height: 3rem; flex-shrink: 0; border-radius: 0.5rem; background: rgba(120, 120, 120, 0.15); display: flex; align-items: center; justify-content: center; font-size: 0.65rem; text-align: center; opacity: 0.6;">
                    {{ __('No image') }}
                </div>
            @endif

            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 600;">
                    @if ($product)
                        <a href="{{ ProductResource::getUrl('edit', ['record' => $product]) }}" style="text-decoration: underline;">
                            {{ $line->product_name }}
                        </a>
                    @else
                        {{ $line->product_name }}
                    @endif
                </div>

                <div style="font-size: 0.8rem; opacity: 0.7;">
                    {{ $line->category_name }} &middot;
                    {{ __('Qty') }}: {{ number_format($line->quantity) }} &middot;
                    {{ __('MOQ') }}: {{ number_format($line->product_moq) }}
                    @if ($line->formattedUnitPrice())
                        &middot; {{ __('Unit price') }}: {{ $line->formattedUnitPrice() }}
                    @endif
                </div>

                @if ($attributes->isNotEmpty() || $line->price_note)
                    <div style="margin-top: 0.375rem; display: flex; gap: 0.375rem; flex-wrap: wrap;">
                        @if ($line->price_note)
                            <x-filament::badge color="info">{{ $line->price_note }}</x-filament::badge>
                        @endif

                        @foreach ($attributes as $attribute)
                            <x-filament::badge color="gray">{{ $attribute }}</x-filament::badge>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @empty
        <p style="opacity: 0.6;">{{ __('No order lines.') }}</p>
    @endforelse
</div>
