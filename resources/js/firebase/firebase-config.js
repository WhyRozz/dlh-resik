import { initializeApp } from "firebase/app";

import {
    getMessaging,
    getToken,
    onMessage
} from "firebase/messaging";

const firebaseConfig = {
  apiKey: "AIzaSyCxq8mp4TJewhdnZoUm12L9uiCrGeyyrzo",
  authDomain: "resik-dlh-nganjuk.firebaseapp.com",
  projectId: "resik-dlh-nganjuk",
  storageBucket: "resik-dlh-nganjuk.firebasestorage.app",
  messagingSenderId: "603548742290",
  appId: "1:603548742290:web:a79b44abefe0d01dac2c4b"
};

const app = initializeApp(firebaseConfig);

const messaging = getMessaging(app);

export { messaging, getToken, onMessage };