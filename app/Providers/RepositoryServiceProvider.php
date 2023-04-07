<?php

namespace App\Providers;

use App\Repositories\Brand\BrandInterface;
use App\Repositories\Brand\BrandRepository;
use App\Repositories\Category\CategoryInterface;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\File\FileInterface;
use App\Repositories\File\FileRepository;
use App\Repositories\OrderStatus\OrderStatusInterface;
use App\Repositories\OrderStatus\OrderStatusRepository;
use App\Repositories\User\UserInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\Product\ProductInterface;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Payment\PaymentInterface;
use App\Repositories\Payment\PaymentRepository;
use App\Repositories\Post\PostInterface;
use App\Repositories\Post\PostRepository;
use App\Repositories\Promotion\PromotionInterface;
use App\Repositories\Promotion\PromotionRepository;
use App\Repositories\Order\OrderInterface;
use App\Repositories\Order\OrderRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(CategoryInterface::class, CategoryRepository::class);
        $this->app->bind(BrandInterface::class, BrandRepository::class);
        $this->app->bind(FileInterface::class, FileRepository::class);
        $this->app->bind(OrderStatusInterface::class, OrderStatusRepository::class);
        $this->app->bind(ProductInterface::class, ProductRepository::class);
        $this->app->bind(PaymentInterface::class, PaymentRepository::class);
        $this->app->bind(PostInterface::class, PostRepository::class);
        $this->app->bind(PromotionInterface::class, PromotionRepository::class);
        $this->app->bind(OrderInterface::class, OrderRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
