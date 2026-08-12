console.log("FCM Loaded");
import { messaging, getToken, onMessage } from "./firebase-config";
import { isSupported } from "firebase/messaging";

const vapidKey = "BMHPRmgvyKfRI9nJU2_g75zpQndgk97Gp9PveWqMivwbhzMKsyBhC-Dn4PXNSX4NswyTnaMx6CIIXy8R62Vur6s";

// ✅ DEVICE ID stabil per browser (kunci supaya 1 device = 1 baris di DB)
function getDeviceId() {
    let id = localStorage.getItem('resik_device_id');
    if (!id) {
        id = (crypto.randomUUID && crypto.randomUUID())
            || ('d-' + Date.now() + '-' + Math.random().toString(16).slice(2));
        localStorage.setItem('resik_device_id', id);
    }
    return id;
}

// ✅ GUARD: cegah init & onMessage terdaftar 2x (INI BIANG KEROK #2)
if (window.__fcmInitialized) {
    console.log("⚠️ FCM sudah di-init sebelumnya, skip duplikat.");
} else {
    window.__fcmInitialized = true;
    initFirebaseNotification();
}

async function initFirebaseNotification() {
    const supported = await isSupported();
    if (!supported) {
        console.log("Firebase Messaging tidak didukung browser ini");
        return;
    }

    try {
        console.log("Registering Service Worker...");
        const registration = await navigator.serviceWorker.register("/firebase-messaging-sw.js");

        const token = await getToken(messaging, {
            vapidKey,
            serviceWorkerRegistration: registration,
        });
        if (!token) { console.log("FCM Token tidak ditemukan"); return; }
        console.log("FCM TOKEN:", token);

              const deviceName = getDeviceName();
        const deviceId   = getDeviceId();
        console.log("📱 Device:", deviceName, "| device_id:", deviceId);

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

        const resp = await fetch("/admin/save-fcm-token", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf,
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },
            body: JSON.stringify({ token: token, device_name: deviceName, device_id: deviceId }),
        });
        console.log("✅ save-fcm-token status:", resp.status);

        // ✅ FOREGROUND: tampil 1x manual (SDK TIDAK auto-display saat tab aktif)
        onMessage(messaging, (payload) => {
            console.log("[FOREGROUND] pesan:", payload);
            const title = payload.notification?.title ?? "RESIK";
            const body  = payload.notification?.body ?? "";
            new Notification(title, { body: body, icon: "/icons/Icon-192.png" });
        });
    } catch (e) {
        console.error("❌ FCM init error:", e);
    }
}

function getDeviceName() {
    const ua = navigator.userAgent;
    let device = 'Unknown Device';
    if (/Windows/i.test(ua)) device = 'Windows PC';
    else if (/Macintosh/i.test(ua)) device = 'Mac';
    else if (/Android/i.test(ua)) device = 'Android';
    else if (/iPhone|iPad|iPod/i.test(ua)) device = 'iOS Device';
    else if (/Linux/i.test(ua)) device = 'Linux';
    if (/Edg/i.test(ua)) device += ' (Edge)';
    else if (/Chrome/i.test(ua) && !/Chromium/i.test(ua)) device += ' (Chrome)';
    else if (/Firefox/i.test(ua)) device += ' (Firefox)';
    else if (/Safari/i.test(ua) && !/Chrome/i.test(ua)) device += ' (Safari)';
    else if (/Opera|OPR/i.test(ua)) device += ' (Opera)';
    return device;
}