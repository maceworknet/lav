<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Total Sales (from orders with status paid, preparing, assigned_to_courier, on_delivery, delivered)
        $completedStatuses = ['paid', 'preparing', 'assigned_to_courier', 'on_delivery', 'delivered'];
        $totalSales = Order::whereIn('status', $completedStatuses)->sum('total');
        
        // Active orders count (not delivered or cancelled or refunded)
        $activeOrders = Order::whereIn('status', ['paid', 'preparing', 'assigned_to_courier', 'on_delivery'])->count();

        // Total orders count
        $totalOrders = Order::count();

        // Total registered customers
        $totalCustomers = Customer::where('is_guest', false)->count();

        return [
            Stat::make('Toplam Satış', '₺' . number_format($totalSales, 2, ',', '.'))
                ->description('Ödenen ve teslim edilen siparişler')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Aktif Siparişler', $activeOrders)
                ->description('Hazırlanan ve yolda olanlar')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
            Stat::make('Toplam Sipariş', $totalOrders)
                ->description('Tüm zamanlardaki siparişler')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make('Kayıtlı Müşteri', $totalCustomers)
                ->description('Üye olan müşteri sayısı')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
