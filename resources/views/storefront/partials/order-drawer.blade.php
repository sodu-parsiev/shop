<div
    x-data
    x-cloak
    x-show="$store.orderBuilder.drawerOpen"
    x-transition.opacity
    @keydown.escape.window="$store.orderBuilder.close()"
    class="fixed inset-0 z-50 flex justify-end bg-black/65"
    role="dialog"
    aria-modal="true"
    aria-label="Заявка на расчёт"
>
    <button type="button" class="absolute inset-0 h-full w-full cursor-default" aria-label="Закрыть заявку" @click="$store.orderBuilder.close()"></button>

    <aside class="relative flex h-full w-full max-w-[540px] flex-col bg-white p-6 text-brand-black shadow-2xl sm:p-8" @click.stop>
        <div class="flex items-start justify-between gap-6 border-b border-brand-black/10 pb-5">
            <div>
                <p class="text-xs font-bold tracking-widest text-brand-pink uppercase">Черновик заказа</p>
                <h2 class="mt-1 text-4xl font-normal">Заявка</h2>
            </div>
            <button type="button" class="text-4xl leading-none" aria-label="Закрыть заявку" @click="$store.orderBuilder.close()">&times;</button>
        </div>

        <div x-show="$store.orderBuilder.lines.length === 0" class="m-auto flex max-w-xs flex-col items-center text-center">
            <img src="{{ asset('brand/mark.png') }}" alt="" class="h-20 w-20">
            <p class="mt-5 text-2xl font-bold">Добавьте товар</p>
            <p class="mt-2 text-sm text-brand-black/60">Выберите складскую футболку или производство под заказ.</p>
            <button type="button" class="mt-6 bg-brand-black px-5 py-3 text-sm font-bold text-white" @click="$store.orderBuilder.close()">Вернуться в каталог</button>
        </div>

        <div x-show="$store.orderBuilder.lines.length > 0" class="flex min-h-0 flex-1 flex-col">
            <div class="min-h-0 flex-1 overflow-y-auto">
                <template x-for="line in $store.orderBuilder.lines" :key="line.product_id">
                    <div class="grid grid-cols-[84px_minmax(0,1fr)_44px] gap-4 border-b border-brand-black/10 py-4">
                        <img :src="line.image" alt="" class="h-24 w-[84px] object-cover">
                        <div class="min-w-0">
                            <p class="font-bold leading-tight" x-text="line.name"></p>
                            <p class="mt-1 text-xs text-brand-black/50"><span x-text="line.availability"></span> · MOQ <span x-text="line.moq.toLocaleString('ru-RU')"></span> шт.</p>
                            <p class="mt-1 text-xs text-brand-black/40" x-text="[line.color, line.density, line.size].filter(Boolean).join(' · ')"></p>
                            <label class="mt-3 block text-xs font-bold tracking-wide text-brand-black/40 uppercase">
                                Количество
                                <input
                                    type="number"
                                    step="1000"
                                    :min="line.moq"
                                    :value="line.quantity"
                                    @change="$store.orderBuilder.updateLineQuantity(line.product_id, $event.target.value)"
                                    class="mt-1 w-full border border-brand-black/15 px-3 py-2 text-sm font-bold"
                                >
                            </label>
                        </div>
                        <button type="button" class="h-11 w-11 text-2xl" :aria-label="`Удалить ${line.name}`" @click="$store.orderBuilder.remove(line.product_id)">&times;</button>
                    </div>
                </template>
            </div>

            <div class="border-t border-brand-black pt-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold tracking-wide text-brand-black/50 uppercase">Позиций в заявке</span>
                    <b class="text-3xl" x-text="$store.orderBuilder.lines.length"></b>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-brand-black/60">Стоимость, сроки и остатки подтвердит менеджер после проверки заказа.</p>
                <a href="#contacts" class="mt-5 flex items-center justify-between bg-brand-pink px-5 py-4 text-sm font-bold text-white" @click="$store.orderBuilder.close()">
                    Перейти к форме
                    <span aria-hidden="true">&#8599;&#65038;</span>
                </a>
            </div>
        </div>
    </aside>
</div>
