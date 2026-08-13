<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Catalog\Size;
use App\Models\Content\Faq;
use App\Models\Content\Page;
use App\Models\User;
use App\Policies\ApplicationPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ColorPolicy;
use App\Policies\CustomizationServicePolicy;
use App\Policies\DensityPolicy;
use App\Policies\FaqPolicy;
use App\Policies\PagePolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductVariantPolicy;
use App\Policies\RolePolicy;
use App\Policies\SizePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn (User $user) => $user->hasRole('Administrator') ? true : null);

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ProductVariant::class, ProductVariantPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Color::class, ColorPolicy::class);
        Gate::policy(Size::class, SizePolicy::class);
        Gate::policy(Density::class, DensityPolicy::class);
        Gate::policy(CustomizationService::class, CustomizationServicePolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Faq::class, FaqPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
    }
}
