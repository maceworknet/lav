@php
    $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
    $pushActive = filter_var($settings['customer_push_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $vapidPublicKey = $settings['customer_push_vapid_public_key'] ?? '';
@endphp

@if($pushActive && !empty($vapidPublicKey))
<div id="customer-push-prompt" class="fixed bottom-6 left-6 z-[9999] max-w-sm bg-white/95 backdrop-blur-md border border-rose-100 rounded-3xl p-5 shadow-2xl transition-all duration-500 transform translate-y-20 opacity-0 hidden">
    <div class="flex gap-4">
        <div class="w-12 h-12 rounded-full bg-rose-50 flex items-center justify-center shrink-0 text-2xl">
            🎁
        </div>
        <div class="space-y-3">
            <h4 class="font-serif font-bold text-slate-800 text-base">Sipariş Bildirimlerini Açın</h4>
            <p class="text-xs text-slate-500 leading-relaxed text-slate-500">Siparişinizin hazırlanma süreci ve durumu ile ilgili anlık bildirimleri açarak, verdiğiniz siparişin hazırlanma, kurye ve teslimat süreçleri hakkında bildirim alabilirsiniz.</p>
            <div class="flex gap-2.5 pt-1">
                <button onclick="subscribeCustomerPush()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-rose-100 transition">Bildirimleri Aç</button>
                <button onclick="dismissCustomerPush()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">Daha Sonra</button>
            </div>
        </div>
    </div>
</div>

<script>
    const pushConfig = {
        vapidPublicKey: "{{ $vapidPublicKey }}",
        orderId: {{ isset($order) ? $order->id : 'null' }}
    };

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function checkPushSubscriptionState() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            return;
        }

        if (Notification.permission === 'denied' || Notification.permission === 'granted') {
            return;
        }

        if (localStorage.getItem('customer_push_dismissed') === 'true') {
            return;
        }

        const path = window.location.pathname;
        const shouldShow = path.includes('/siparis-basarili') || path.includes('/hesabim') || path.includes('/kayit') || path.includes('/giris');

        if (shouldShow) {
            const prompt = document.getElementById('customer-push-prompt');
            if (prompt) {
                prompt.classList.remove('hidden');
                setTimeout(() => {
                    prompt.classList.remove('opacity-0', 'translate-y-20');
                }, 2000);
            }
        }
    }

    function subscribeCustomerPush() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

        Notification.requestPermission().then(permission => {
            if (permission !== 'granted') {
                dismissCustomerPush();
                return;
            }

            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    const subscribeOptions = {
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(pushConfig.vapidPublicKey)
                    };
                    return registration.pushManager.subscribe(subscribeOptions);
                })
                .then(subscription => {
                    const subData = subscription.toJSON();
                    if (pushConfig.orderId) {
                        subData.order_id = pushConfig.orderId;
                    }
                    
                    return fetch('/api/push-subscribe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(subData)
                    });
                })
                .then(res => res.json())
                .then(data => {
                    console.log("Web Push subscription registered successfully: ", data);
                    dismissCustomerPush();
                })
                .catch(err => {
                    console.error("Failed to subscribe customer to Web Push: ", err);
                    dismissCustomerPush();
                });
        });
    }

    function dismissCustomerPush() {
        const prompt = document.getElementById('customer-push-prompt');
        if (prompt) {
            prompt.classList.add('opacity-0', 'translate-y-20');
            localStorage.setItem('customer_push_dismissed', 'true');
            setTimeout(() => {
                prompt.classList.add('hidden');
            }, 500);
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        if ('serviceWorker' in navigator && 'PushManager' in window && Notification.permission === 'granted') {
            navigator.serviceWorker.register('/sw.js').then(reg => {
                reg.pushManager.getSubscription().then(sub => {
                    if (sub) {
                        const subData = sub.toJSON();
                        if (pushConfig.orderId) {
                            subData.order_id = pushConfig.orderId;
                        }
                        fetch('/api/push-subscribe', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(subData)
                        }).catch(e => console.warn("Failed to update active subscription token: ", e));
                    }
                });
            });
        } else {
            setTimeout(checkPushSubscriptionState, 1500);
        }
    });
</script>
@endif
