console.log("FCM Loaded");
import { messaging, getToken, onMessage } from "./firebase-config";
import { isSupported } from "firebase/messaging";

const vapidKey =
    "BMHPRmgvyKfRI9nJU2_g75zpQndgk97Gp9PveWqMivwbhzMKsyBhC-Dn4PXNSX4NswyTnaMx6CIIXy8R62Vur6s";

async function initFirebaseNotification() {

    const supported = await isSupported();

    if (!supported) {
        console.log("Firebase Messaging tidak didukung browser ini");
        return;
    }

    try {

        console.log("Registering Service Worker...");

        const registration = await navigator.serviceWorker.register(
            "/firebase-messaging-sw.js"
        );

        const token = await getToken(messaging, {
            vapidKey,
            serviceWorkerRegistration: registration,
        });

        if (!token) {
            console.log("FCM Token tidak ditemukan");
            return;
        }

        console.log("FCM TOKEN:", token);

        // ✅ DETEKSI DEVICE NAME
        const deviceName = getDeviceName();
        console.log("📱 Device Name:", deviceName);

        await fetch("/admin/save-fcm-token", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
            },
            body: JSON.stringify({
                token: token,
                device_name: deviceName, // ✅ KIRIM DEVICE NAME
            }),
        });

        console.log("✅ Token berhasil dikirim ke server");

        // ✅ PINDAHKAN onMessage KE SINI
        onMessage(messaging, (payload) => {
            console.log(payload);

            new Notification(payload.notification.title, {
                body: payload.notification.body,
                icon: "/favicon.ico",
            });
        });

    } catch (e) {
        console.error(e);
    }
}

initFirebaseNotification();


// ✅ HELPER: DETEKSI NAMA DEVICE
function getDeviceName() {
    const ua = navigator.userAgent;
    let device = 'Unknown Device';

    // Deteksi OS
    if (/Windows/i.test(ua)) device = 'Windows PC';
    else if (/Macintosh/i.test(ua)) device = 'Mac';
    else if (/Android/i.test(ua)) device = 'Android';
    else if (/iPhone|iPad|iPod/i.test(ua)) device = 'iOS Device';
    else if (/Linux/i.test(ua)) device = 'Linux';

    // Deteksi Browser
    if (/Edg/i.test(ua)) device += ' (Edge)';
    else if (/Chrome/i.test(ua) && !/Chromium/i.test(ua)) device += ' (Chrome)';
    else if (/Firefox/i.test(ua)) device += ' (Firefox)';
    else if (/Safari/i.test(ua) && !/Chrome/i.test(ua)) device += ' (Safari)';
    else if (/Opera|OPR/i.test(ua)) device += ' (Opera)';

    return device;
}