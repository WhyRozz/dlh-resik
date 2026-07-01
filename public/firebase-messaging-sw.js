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

messaging.onBackgroundMessage(function (payload) {

    console.log("Background Message:", payload);

    const title =
        payload.notification?.title ??
        "RESIK";

    const options = {
        body: payload.notification?.body,
        icon: "/favicon.ico",
        badge: "/favicon.ico"
    };

    self.registration.showNotification(title, options);
});

