import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  metadataBase: new URL("https://svoi-hod-b2b.minikhanmia.chatgpt.site"),
  title: "Свой Ход — футболки оптом от 5 000 штук для WB и Ozon",
  description: "Белые и чёрные базовые футболки со склада, другие цвета и модели — под заказ. Серийное производство партий от 5 000 штук для продавцов Wildberries, Ozon и брендов.",
  keywords: ["футболки оптом от 5000 штук", "базовые футболки оптом", "одежда для Wildberries", "одежда для Ozon", "контрактное производство одежды", "футболки под бренд", "пошив одежды крупным оптом"],
  alternates: { canonical: "/" },
  openGraph: { type: "website", locale: "ru_RU", title: "Свой Ход — база, на которой строятся бренды", description: "Футболки со склада и серийное производство базовой одежды партиями от 5 000 штук.", images: [{ url: "/og.png", width: 1200, height: 630, alt: "Свой Ход — оптовое производство базовой одежды" }] },
  twitter: { card: "summary_large_image", title: "Свой Ход", description: "База, на которой строятся бренды", images: ["/og.png"] },
  icons: { icon: "/brand/mark.png", apple: "/brand/mark.png" },
  robots: { index: true, follow: true },
};

const schema = {
  "@context": "https://schema.org",
  "@graph": [
    { "@type": "Organization", "@id": "https://svojkhod.ru/#organization", name: "Свой Ход", url: "https://svojkhod.ru", email: "info@svojkhod.ru", description: "Производитель базовой одежды крупным оптом для продавцов маркетплейсов", areaServed: { "@type": "Country", name: "Россия" }, knowsAbout: ["Оптовый пошив одежды", "Футболки для Wildberries", "Футболки для Ozon", "Шелкография", "Вышивка", "DTF-печать"] },
    { "@type": "FAQPage", mainEntity: [
      { "@type": "Question", name: "Какой минимальный заказ?", acceptedAnswer: { "@type": "Answer", text: "Минимальная производственная партия — 5 000 изделий. Параметры серии фиксируются в спецификации." } },
      { "@type": "Question", name: "Что сейчас есть на складе?", acceptedAnswer: { "@type": "Answer", text: "На складе доступны белые и чёрные базовые футболки. Размерные остатки подтверждаются перед заказом." } },
      { "@type": "Question", name: "Можно ли заказать другие цвета?", acceptedAnswer: { "@type": "Answer", text: "Да. Другие цвета, плотности и модели производятся под заказ после согласования образца." } },
      { "@type": "Question", name: "Почему на сайте нет фиксированных цен?", acceptedAnswer: { "@type": "Answer", text: "Цена зависит от ткани, модели, объёма, нанесения, упаковки и логистики и фиксируется в коммерческом предложении." } },
      { "@type": "Question", name: "Можно ли заказать печать или вышивку?", acceptedAnswer: { "@type": "Answer", text: "Да. Доступны шелкография, вышивка, DTF и термопечать, а для крупных заказов — разработка лекал." } }
    ] }
  ]
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return <html lang="ru"><body>{children}<script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(schema) }} /></body></html>;
}
