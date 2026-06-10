@if(auth()->check())
@php
    $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
    $active = filter_var($settings['admin_audio_notification_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $desktopActive = filter_var($settings['admin_desktop_notification_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $bellSound = $settings['admin_notification_bell_sound'] ?? 'assets/audio/bell.mp3';
    $volume = (float)($settings['admin_notification_volume'] ?? 1.0);
    $interval = (int)($settings['admin_notification_polling_interval'] ?? 15);
@endphp

@if($active || $desktopActive)
<style>
    #admin-notif-permission-banner {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 99999;
        max-width: 360px;
        background-color: #ffffff;
        border: 1px solid #ffe4e6;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
        transform: translateY(100px);
        opacity: 0;
        display: none;
    }
    #admin-notif-permission-banner.show {
        display: block;
    }
    #admin-notif-permission-banner.visible {
        transform: translateY(0);
        opacity: 1;
    }
    .admin-notif-flex {
        display: flex;
        align-items: start;
        gap: 12px;
    }
    .admin-notif-btn-group {
        display: flex;
        gap: 10px;
        margin-top: 12px;
    }
    .admin-notif-btn-allow {
        background-color: #e11d48;
        color: #ffffff;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 6px -1px rgba(225, 29, 72, 0.2);
        transition: background-color 0.2s;
    }
    .admin-notif-btn-allow:hover {
        background-color: #be123c;
    }
    .admin-notif-btn-later {
        background-color: #f1f5f9;
        color: #475569;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .admin-notif-btn-later:hover {
        background-color: #e2e8f0;
    }
</style>

<div id="admin-notif-permission-banner">
    <div class="admin-notif-flex">
        <span style="font-size: 20px;">🔔</span>
        <div style="flex: 1;">
            <h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 700; color: #1e293b; font-family: sans-serif;">Sipariş Bildirimlerini Aktifleştirin</h4>
            <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.5; font-family: sans-serif;">Yeni sipariş geldiğinde sesli uyarı ve masaüstü bildirimleri almak için izin verin.</p>
            <div class="admin-notif-btn-group">
                <button onclick="requestAdminNotifPermission()" class="admin-notif-btn-allow">İzin Ver</button>
                <button onclick="dismissAdminNotifBanner()" class="admin-notif-btn-later">Daha Sonra</button>
            </div>
        </div>
    </div>
</div>

<script>
    const notifConfig = {
        audioActive: {{ $active ? 'true' : 'false' }},
        desktopActive: {{ $desktopActive ? 'true' : 'false' }},
        bellSound: "{{ asset($bellSound) }}",
        volume: {{ $volume }},
        interval: {{ $interval * 1000 }}
    };

    let audioCtx = null;

    function playBell() {
        if (!notifConfig.audioActive) return;
        
        let played = false;
        if (notifConfig.bellSound) {
            try {
                const audio = new Audio(notifConfig.bellSound);
                audio.volume = notifConfig.volume;
                audio.play()
                    .then(() => { played = true; })
                    .catch(err => {
                        console.warn("Audio file playback blocked or failed, using synthesized chime: ", err);
                        playSynthChime();
                    });
            } catch (e) {
                playSynthChime();
            }
        } else {
            playSynthChime();
        }
    }

    function playSynthChime() {
        try {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            const osc1 = audioCtx.createOscillator();
            const osc2 = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
            osc2.type = 'triangle';
            osc2.frequency.setValueAtTime(1200, audioCtx.currentTime); // Harmony
            
            gainNode.gain.setValueAtTime(notifConfig.volume, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 1.5);
            
            osc1.connect(gainNode);
            osc2.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            osc1.start();
            osc2.start();
            osc1.stop(audioCtx.currentTime + 1.5);
            osc2.stop(audioCtx.currentTime + 1.5);
        } catch (e) {
            console.error("Synth chime failure: ", e);
        }
    }

    function showNotification(title, message, orderId) {
        if (notifConfig.desktopActive && Notification.permission === 'granted') {
            const options = {
                body: message,
                icon: '/gift-shop.svg',
                tag: 'new-order-' + orderId,
                requireInteraction: true
            };
            const notif = new Notification(title, options);
            notif.onclick = function() {
                window.focus();
                window.location.href = '/admin/orders/' + orderId;
            };
        }
    }

    function checkNewOrders() {
        fetch('/admin/api/new-orders')
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    playBell();
                    
                    data.forEach(notif => {
                        showNotification(notif.title, notif.message, notif.order_id);
                        
                        if (window.Filament) {
                            window.Filament.notify('success', notif.message, {
                                title: notif.title,
                                duration: 10000
                            });
                        }
                    });
                    
                    fetch('/admin/api/mark-notifications-seen', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).catch(err => console.error("Mark seen error: ", err));
                }
            })
            .catch(err => console.error("New orders polling error: ", err));
    }

    function requestAdminNotifPermission() {
        if ('Notification' in window) {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    if (!audioCtx) {
                        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    dismissAdminNotifBanner();
                    playBell();
                }
            });
        }
    }

    function dismissAdminNotifBanner() {
        const banner = document.getElementById('admin-notif-permission-banner');
        if (banner) {
            banner.classList.remove('visible');
            setTimeout(() => {
                banner.classList.remove('show');
            }, 500);
        }
    }

    function initAdminNotif() {
        if ('Notification' in window) {
            if (Notification.permission === 'default' && notifConfig.desktopActive) {
                const banner = document.getElementById('admin-notif-permission-banner');
                if (banner) {
                    banner.classList.add('show');
                    setTimeout(() => {
                        banner.classList.add('visible');
                    }, 100);
                }
            }
        }

        if (notifConfig.audioActive || notifConfig.desktopActive) {
            setTimeout(checkNewOrders, 2000);
            setInterval(checkNewOrders, notifConfig.interval);
        }
    }

    if (document.readyState === 'loading') {
        window.addEventListener('DOMContentLoaded', initAdminNotif);
    } else {
        initAdminNotif();
    }
</script>
@endif
@endif
