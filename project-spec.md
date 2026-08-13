Stack:
- Laravel 12
- Filament for admin panel
- Blade + Livewire/Alpine for public frontend
- MySQL
- Queue for external integrations and email

Important business rules:
- This is a B2B catalog, not an ecommerce checkout.
- There is no online payment for now.
- There is no automatic final price calculation for now.
- Products have variants based on:
    - color
    - density
    - size
- Availability and stock quantity belong to the exact variant.
- Variant can be "in stock" or "made to order".
- Product has configurable MOQ.
- MOQ is not summed between positions.
- Customer specifies quantity for each selected size/variant.
- Requests are stored locally.
- Requests are sent to Bitrix24.
- Requests are also sent by email.
- 1C integration is NOT part of the current implementation.
- Architecture should allow adding 1C later.
- Reports/applications must be exportable to CSV/XLSX.
- Automatic regular backups are required.
