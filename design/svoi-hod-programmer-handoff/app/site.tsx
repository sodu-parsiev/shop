"use client";

import { useMemo, useState } from "react";

type Availability = "stock" | "order";

type Product = {
  id: number;
  name: string;
  category: "Футболки" | "Другие модели";
  availability: Availability;
  image: string;
  colors: string[];
  density: string;
  sizes: string;
  description: string;
};

type RequestLine = {
  product: Product;
  quantity: number;
  density: string;
  size: string;
};

const MIN_ORDER = 5000;

const products: Product[] = [
  {
    id: 1,
    name: "Базовая футболка — белая",
    category: "Футболки",
    availability: "stock",
    image: "/brand/catalog-white-v2.jpg",
    colors: ["Белый"],
    density: "По текущей складской партии",
    sizes: "Размерный ряд и остатки — по запросу",
    description: "Готовая партия базовых футболок без принта. Можно заказать брендирование, упаковку и маркировку.",
  },
  {
    id: 2,
    name: "Базовая футболка — чёрная",
    category: "Футболки",
    availability: "stock",
    image: "/brand/catalog-black-v2.jpg",
    colors: ["Чёрный"],
    density: "По текущей складской партии",
    sizes: "Размерный ряд и остатки — по запросу",
    description: "Готовая партия чёрных футболок из хлопка. Подходит для продаж без принта или нанесения вашего бренда.",
  },
  {
    id: 3,
    name: "Футболки в цвете бренда",
    category: "Футболки",
    availability: "order",
    image: "/brand/catalog-colors-v2.jpg",
    colors: ["Молочный", "Графит", "Хаки", "Синий", "Розовый"],
    density: "180 / 200 / 240 gsm",
    sizes: "Размерная сетка по спецификации",
    description: "Пошив партии в нужном цвете, плотности и посадке. Перед запуском согласуем образец и параметры ткани.",
  },
  {
    id: 4,
    name: "Футболка Heavy Oversize",
    category: "Футболки",
    availability: "order",
    image: "/brand/model-close.jpg",
    colors: ["Цвет по ТЗ"],
    density: "240 gsm",
    sizes: "Размерная сетка по спецификации",
    description: "Плотная футболка свободной посадки для премиальной линейки маркетплейса. Производство под заказ.",
  },
  {
    id: 5,
    name: "Худи, свитшоты и лонгсливы",
    category: "Другие модели",
    availability: "order",
    image: "/brand/model-motion.jpg",
    colors: ["Цвет по ТЗ"],
    density: "По модели и ТЗ",
    sizes: "Индивидуальная размерная сетка",
    description: "Разработаем или адаптируем модель под ваш бренд. Лекала, образец, пошив, упаковка и маркировка партии.",
  },
];

const swatch: Record<string, string> = {
  "Белый": "#ffffff",
  "Чёрный": "#0d0802",
  "Молочный": "#e2dece",
  "Графит": "#43433e",
  "Хаки": "#8a876c",
  "Синий": "#1e315d",
  "Розовый": "#e51872",
  "Цвет по ТЗ": "linear-gradient(135deg,#e51872,#e2dece,#43433e)",
};

function productWord(count: number) {
  if (count === 1) return "модель";
  if (count > 1 && count < 5) return "модели";
  return "моделей";
}

export function BrandSite() {
  const [availability, setAvailability] = useState<"all" | Availability>("all");
  const [category, setCategory] = useState("Все");
  const [quantity, setQuantity] = useState(MIN_ORDER);
  const [preferredDensity, setPreferredDensity] = useState("Уточнить с менеджером");
  const [preferredSize, setPreferredSize] = useState("Смешанная размерная сетка");
  const [requestLines, setRequestLines] = useState<RequestLine[]>([]);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [draftReady, setDraftReady] = useState(false);

  const filtered = useMemo(
    () => products.filter((product) =>
      (availability === "all" || product.availability === availability) &&
      (category === "Все" || product.category === category)),
    [availability, category],
  );

  const addToRequest = (product: Product) => {
    setRequestLines((current) => [
      ...current,
      { product, quantity: Math.max(MIN_ORDER, quantity), density: preferredDensity, size: preferredSize },
    ]);
    setDrawerOpen(true);
  };

  return (
    <main id="content">
      <a className="skip-link" href="#catalog">Перейти к каталогу</a>
      <div className="topbar">Партии от 5 000 штук <span>• Белые и чёрные футболки на складе</span></div>

      <header className="header">
        <a href="#top" className="brand" aria-label="Свой Ход — на главную">
          <img src="/brand/logo-dark.png" alt="Свой Ход" />
        </a>
        <button className="menu-button" aria-expanded={mobileOpen} aria-controls="main-nav" onClick={() => setMobileOpen(!mobileOpen)}>Меню</button>
        <nav id="main-nav" className={mobileOpen ? "nav open" : "nav"} aria-label="Главное меню">
          <a href="#catalog" onClick={() => setMobileOpen(false)}>Каталог</a>
          <a href="#production" onClick={() => setMobileOpen(false)}>Производство</a>
          <a href="#custom" onClick={() => setMobileOpen(false)}>Кастомизация</a>
          <a href="#terms" onClick={() => setMobileOpen(false)}>Условия</a>
          <a href="#contacts" onClick={() => setMobileOpen(false)}>Контакты</a>
        </nav>
        <button className="request-button" onClick={() => setDrawerOpen(true)}>Заявка <b>{requestLines.length}</b></button>
      </header>

      <section className="hero" id="top">
        <div className="hero-copy">
          <div className="eyebrow"><span>Производство рядом с хлопком</span><span>B2B</span></div>
          <h1>База, на которой <em>строятся бренды</em></h1>
          <p>Оптовое производство базовой одежды для продавцов маркетплейсов. Партии от 5 000 изделий, контроль качества и конфигурация под ваш бренд.</p>
          <div className="stock-note"><i />Белые и чёрные футболки — на складе. Другие цвета и модели производим под заказ.</div>
          <div className="hero-actions">
            <a className="button primary" href="#catalog">Выбрать модель <span>↗</span></a>
            <a className="button secondary" href="#contacts">Запросить расчёт</a>
          </div>
          <div className="hero-proof">
            <div><strong>от 5 000</strong><span>изделий в партии</span></div>
            <div><strong>склад + пошив</strong><span>два формата заказа</span></div>
            <div><strong>по договору</strong><span>для ИП и юрлиц</span></div>
          </div>
        </div>
        <div className="hero-visual">
          <img src="/brand/model-motion.jpg" alt="Базовая хлопковая футболка производства Свой Ход" />
          <div className="hero-card"><img src="/brand/mark.png" alt="" /><span>От сырья<br/><b>до готовой партии</b></span></div>
          <div className="density-tag"><b>180–340</b> gsm под заказ</div>
        </div>
      </section>

      <section className="ticker" aria-label="Преимущества"><div>БЕЗ ПОСРЕДНИКОВ <i>✦</i> КОНТРОЛЬ КАЧЕСТВА <i>✦</i> ПАРТИИ ОТ 5 000 ШТ. <i>✦</i> ЦЕНА ПО СПЕЦИФИКАЦИИ</div></section>

      <section className="why section-pad">
        <div className="section-heading"><span className="section-num">01</span><div><p>Почему «Свой Ход»</p><h2>Снижаем издержки,<br/><em>сохраняем качество.</em></h2></div></div>
        <div className="why-grid">
          <article><span>01</span><h3>Рядом с сырьём</h3><p>Производство расположено рядом с хлопковыми полями. Меньше лишней логистики — устойчивее себестоимость.</p></article>
          <article className="dark-card"><span>02</span><h3>Партии для масштаба</h3><p>Работаем с объёмами от 5 000 изделий — для брендов и продавцов, которым нужна стабильность серийного производства.</p></article>
          <article><span>03</span><h3>По спецификации</h3><p>Цвет, плотность, размерную сетку, маркировку и упаковку фиксируем до запуска партии.</p></article>
        </div>
      </section>

      <section className="catalog section-pad" id="catalog">
        <div className="section-heading inverse"><span className="section-num">02</span><div><p>Каталог и производство</p><h2>Выберите формат <em>партии</em></h2></div><span className="found">{filtered.length} {productWord(filtered.length)}</span></div>

        <div className="availability-tabs" role="group" aria-label="Наличие товара">
          <button className={availability === "all" ? "active" : ""} onClick={() => setAvailability("all")}>Все модели</button>
          <button className={availability === "stock" ? "active" : ""} onClick={() => setAvailability("stock")}><i className="stock-dot" />На складе</button>
          <button className={availability === "order" ? "active" : ""} onClick={() => setAvailability("order")}>Под заказ</button>
        </div>

        <div className="catalog-config">
          <label>Категория<select value={category} onChange={(event) => setCategory(event.target.value)}><option>Все</option><option>Футболки</option><option>Другие модели</option></select></label>
          <label>Желаемая плотность<select value={preferredDensity} onChange={(event) => setPreferredDensity(event.target.value)}><option>Уточнить с менеджером</option><option>180 gsm</option><option>200 gsm</option><option>240 gsm</option><option>320–340 gsm</option></select></label>
          <label>Размерная сетка<select value={preferredSize} onChange={(event) => setPreferredSize(event.target.value)}><option>Смешанная размерная сетка</option><option>XS–XL</option><option>S–2XL</option><option>Разработать под бренд</option></select></label>
          <label className="quantity-control">Объём партии<div><input type="number" inputMode="numeric" min={MIN_ORDER} step="1000" value={quantity} onChange={(event) => setQuantity(Math.max(MIN_ORDER, Number(event.target.value) || MIN_ORDER))}/><span>шт.</span></div></label>
        </div>
        <div className="quantity-presets" aria-label="Быстрый выбор объёма">{[5000,10000,25000].map((value) => <button key={value} className={quantity === value ? "active" : ""} onClick={() => setQuantity(value)}>{value.toLocaleString("ru-RU")} шт.</button>)}<span>Цена рассчитывается после согласования ткани, модели и комплектации.</span></div>

        <div className="product-grid">
          {filtered.map((product) => (
            <article className="product-card" key={product.id}>
              <div className="product-image">
                <img src={product.image} alt={`${product.name} — базовая одежда оптом`} loading="lazy" />
                <span className={`status ${product.availability}`}>{product.availability === "stock" ? "На складе" : "Под заказ"}</span>
              </div>
              <div className="product-body">
                <div className="product-meta"><span>{product.category}</span><b>от 5 000 шт.</b></div>
                <h3>{product.name}</h3>
                <p>{product.description}</p>
                <dl><div><dt>Плотность</dt><dd>{product.density}</dd></div><div><dt>Размеры</dt><dd>{product.sizes}</dd></div></dl>
                <div className="swatches" aria-label="Доступные цвета">{product.colors.map((color) => <span key={color}><i style={{background: swatch[color]}} />{color}</span>)}</div>
                <div className="product-buy"><div><small>Стоимость партии</small><strong>По запросу</strong><small>фиксируется в спецификации</small></div><button onClick={() => addToRequest(product)}>{product.availability === "stock" ? "Запросить остатки" : "Рассчитать пошив"}<span>↗</span></button></div>
              </div>
            </article>
          ))}
          {filtered.length === 0 && <div className="empty-state"><b>По выбранным параметрам моделей нет</b><p>Измените фильтры или отправьте техническое задание — предложим вариант производства.</p><button onClick={() => {setAvailability("all");setCategory("Все");}}>Сбросить фильтры</button></div>}
        </div>
      </section>

      <section className="marketplace section-pad">
        <div className="marketplace-copy"><span className="section-num">03</span><p className="kicker">Для продавцов маркетплейсов</p><h2>Не просто поставщик.<br/><em>Производственная база.</em></h2><p>Помогаем подготовить крупную партию к выходу на площадку: согласуем размерную матрицу, упаковку, этикетки и маркировку.</p><a className="text-link" href="#contacts">Обсудить техническое задание <span>↗</span></a></div>
        <div className="marketplace-list">{["Белые и чёрные футболки со склада", "Другие цвета — пошив под заказ", "Плотности ткани под задачу", "Упаковка, этикетки и маркировка", "Повтор серии по спецификации"].map((item,index)=><div key={item}><span>0{index+1}</span><b>{item}</b><i>→</i></div>)}</div>
      </section>

      <section className="production section-pad" id="production">
        <div className="production-photo"><img src="/brand/fabric-touch.jpg" alt="Проверка качества хлопкового полотна" loading="lazy"/><span>Контроль полотна вручную</span></div>
        <div className="production-copy"><span className="section-num">04</span><p className="kicker">О производстве</p><h2>Все идут привычным путём.<br/><em>Мы сделали свой ход.</em></h2><p>Мы разместили производство ближе к хлопковым полям, сократили цепочку и сохранили контроль качества. Это позволяет планировать крупные партии без лишних посредников.</p><div className="route"><div><b>Хлопок</b><span>сырьё на месте</span></div><i>→</i><div><b>Полотно</b><span>контроль партии</span></div><i>→</i><div><b>Пошив</b><span>серийный цех</span></div><i>→</i><div><b>Россия</b><span>прямая доставка</span></div></div></div>
      </section>

      <section className="custom section-pad" id="custom">
        <div className="section-heading"><span className="section-num">05</span><div><p>Печать и кастомизация</p><h2>Ваш бренд.<br/><em>Наша производственная система.</em></h2></div></div>
        <div className="custom-grid">
          <article className="custom-feature"><div><span>01</span><h3>Шелкография</h3><p>Стойкое нанесение для крупных тиражей и точного повторения фирменного цвета.</p></div><div className="print-demo">СВОЙ<br/>ХОД</div></article>
          {[["02","Вышивка","Фактурное нанесение логотипа и надписей для премиальных линеек."],["03","DTF / термопечать","Полноцветные изображения и быстрый запуск после согласования макета."],["04","Разработка лекал","Собственная посадка, образец и градация размерного ряда для серии."]].map((item)=><article key={item[0]}><span>{item[0]}</span><h3>{item[1]}</h3><p>{item[2]}</p><a href="#contacts">Обсудить <i>↗</i></a></article>)}
        </div>
      </section>

      <section className="terms section-pad" id="terms">
        <div className="section-heading inverse"><span className="section-num">06</span><div><p>Условия сотрудничества</p><h2>Понятно на каждом <em>этапе</em></h2></div></div>
        <div className="terms-grid"><div><strong>5 000</strong><span>изделий</span><p>минимальный объём одной производственной партии</p></div><div><strong>Склад</strong><span>белый и чёрный</span><p>готовые футболки — после подтверждения размерных остатков</p></div><div><strong>Под заказ</strong><span>цвет и модель</span><p>срок фиксируется после согласования образца и спецификации</p></div><div><strong>Цена</strong><span>индивидуально</span><p>зависит от ткани, комплектации, нанесения и объёма</p></div></div>
      </section>

      <section className="faq section-pad" id="faq">
        <div className="section-heading"><span className="section-num">?</span><div><p>Коротко о важном</p><h2>Вопросы от <em>продавцов</em></h2></div></div>
        <div className="faq-list"><details open><summary>Какой минимальный заказ?<span>+</span></summary><p>Минимальная производственная партия — 5 000 изделий. Параметры одной серии фиксируются в спецификации.</p></details><details><summary>Что сейчас есть на складе?<span>+</span></summary><p>Белые и чёрные базовые футболки. Актуальные остатки по размерам подтвердит менеджер перед оформлением заказа.</p></details><details><summary>Можно заказать другие цвета?<span>+</span></summary><p>Да. Другие цвета, плотности и посадки производятся под заказ после согласования образца.</p></details><details><summary>Почему на сайте нет фиксированных цен?<span>+</span></summary><p>Стоимость крупной партии зависит от полотна, плотности, модели, нанесения, упаковки и логистики. Цена фиксируется в коммерческом предложении и договоре.</p></details></div>
      </section>

      <section className="account section-pad" id="account"><div className="account-panel"><img src="/brand/mark.png" alt=""/><div><p className="kicker">B2B-сервис</p><h2>Личный кабинет — следующим этапом</h2><p>После подключения учётной системы здесь появятся история заказов, повтор серии, документы и статус производства. Сейчас все параметры фиксируются в договоре и спецификации.</p></div><a href="#contacts">Начать с расчёта партии <span>↗</span></a></div></section>

      <section className="contact section-pad" id="contacts">
        <div className="contact-copy"><span className="section-num">07</span><p className="kicker">Начать сотрудничество</p><h2>Пора сделать<br/><em>свой ход.</em></h2><p>Сформируйте черновик заявки. Для точного расчёта потребуются модель, объём, плотность, размерная сетка, нанесение и упаковка.</p><div className="contact-links"><a href="mailto:info@svojkhod.ru">info@svojkhod.ru</a><span>Московская область, Котельники,<br/>Дзержинское шоссе, 13</span></div></div>
        <form className="lead-form" onSubmit={(event)=>{event.preventDefault();setDraftReady(true);}}>
          {draftReady ? <div className="success"><img src="/brand/mark.png" alt=""/><h3>Черновик заявки готов</h3><p>Отправка в CRM подключается отдельно. Сейчас продублируйте параметры партии на электронную почту отдела производства.</p><a href="mailto:info@svojkhod.ru?subject=Запрос на расчёт партии от 5000 шт.">Написать на info@svojkhod.ru <span>↗</span></a><button type="button" onClick={()=>setDraftReady(false)}>Изменить заявку</button></div> : <>
            <div className="form-row"><label>Компания<input required name="company" placeholder="Название компании или ИП"/></label><label>Контактное лицо<input required name="name" placeholder="Как к вам обращаться"/></label></div>
            <div className="form-row"><label>Телефон<input required name="phone" type="tel" inputMode="tel" placeholder="+7 (___) ___-__-__"/></label><label>Объём<select name="quantity" defaultValue="5000"><option value="5000">5 000–10 000 шт.</option><option value="10000">10 000–25 000 шт.</option><option value="25000">Более 25 000 шт.</option></select></label></div>
            <label>Комментарий<textarea name="comment" placeholder="Модель, цвет, плотность, размеры, нанесение и упаковка" rows={4}/></label>
            <button className="form-submit" type="submit">Сформировать заявку <span>↗</span></button>
            <small>Форма создаёт черновик. Отправка менеджеру будет подключена вместе с CRM.</small>
          </>}
        </form>
      </section>

      <footer><div className="footer-logo"><img src="/brand/logo.png" alt="Свой Ход"/><p>Серийное производство базовой одежды для продавцов маркетплейсов.</p></div><div><b>Навигация</b><a href="#catalog">Каталог</a><a href="#production">Производство</a><a href="#custom">Кастомизация</a><a href="#terms">Условия</a></div><div><b>Заказ</b><a href="#catalog">На складе</a><a href="#catalog">Под заказ</a><a href="#contacts">Запросить расчёт</a><a href="#faq">Вопросы</a></div><div><b>Контакты</b><a href="mailto:info@svojkhod.ru">info@svojkhod.ru</a><span>Котельники,<br/>Дзержинское шоссе, 13</span></div><div className="footer-bottom"><span>© 2026 «Свой Ход»</span><span>Сделано для тех, кто строит своё.</span></div></footer>

      {drawerOpen && <div className="drawer-backdrop" onClick={() => setDrawerOpen(false)}><aside className="request-drawer" onClick={(event)=>event.stopPropagation()} aria-label="Заявка на расчёт"><div className="drawer-head"><div><span>Предварительная спецификация</span><h2>Заявка</h2></div><button onClick={()=>setDrawerOpen(false)} aria-label="Закрыть заявку">×</button></div>{requestLines.length === 0 ? <div className="drawer-empty"><img src="/brand/mark.png" alt=""/><b>Добавьте модель</b><p>Выберите складскую футболку или производство под заказ.</p><button onClick={()=>setDrawerOpen(false)}>Вернуться в каталог</button></div> : <><div className="request-items">{requestLines.map((line,index)=><div className="request-item" key={`${line.product.id}-${index}`}><img src={line.product.image} alt=""/><div><b>{line.product.name}</b><span>{line.product.availability === "stock" ? "На складе" : "Под заказ"} · {line.quantity.toLocaleString("ru-RU")} шт.</span><small>{line.density}<br/>{line.size}</small></div><button onClick={()=>setRequestLines(lines=>lines.filter((_,itemIndex)=>itemIndex!==index))} aria-label={`Удалить ${line.product.name}`}>×</button></div>)}</div><div className="request-summary"><span>Позиций в заявке</span><b>{requestLines.length}</b><small>Стоимость, сроки и доступные размеры будут подтверждены после проверки спецификации.</small><a href="#contacts" onClick={()=>setDrawerOpen(false)}>Перейти к форме <span>↗</span></a></div></>}</aside></div>}
    </main>
  );
}
