importScripts("https://www.gstatic.com/firebasejs/10.13.2/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/10.13.2/firebase-messaging-compat.js");

firebase.initializeApp({
    apiKey: "AIzaSyCxq8mp4TJewhdnZoUm12L9uiCrGeyyrzo",
    authDomain: "resik-dlh-nganjuk.firebaseapp.com",
    projectId: "resik-dlh-nganjuk",
    storageBucket: "resik-dlh-nganjuk.firebasestorage.app",
    messagingSenderId: "603548742290",
    appId: "1:603548742290:web:a79b44abefe0d01dac2c4b"
});

const messaging = firebase.messaging();

// ⛔ JANGAN panggil self.registration.showNotification() di sini!
// Firebase SDK SUDAH otomatis menampilkan notifikasi dari payload "notification".
// Kalau kita tampilkan manual lagi di sini = muncul 2x (INI BIANG KEROK #1).
messaging.onBackgroundMessage((payload) => {
    console.log("[SW] background message (ditampilkan otomatis oleh SDK):", payload);
});