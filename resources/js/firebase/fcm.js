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