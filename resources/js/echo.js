import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const pusherKey = document
    .querySelector('meta[name="pusher-key"]')
    ?.getAttribute("content");
const pusherCluster = document
    .querySelector('meta[name="pusher-cluster"]')
    ?.getAttribute("content");

console.log("Pusher Key:", pusherKey);
console.log("Pusher Cluster:", pusherCluster);

if (pusherKey && pusherCluster) {
    window.Echo = new Echo({
        broadcaster: "pusher",
        key: pusherKey,
        cluster: pusherCluster,
        forceTLS: true,
        encrypted: true,
        disableStats: true,
    });

    console.log("✅ Pusher connected");

    // Listen for withdrawal events
    window.Echo.channel("admin-notifications").listen(
        ".new-withdrawal",
        (data) => {
            console.log("🔔 New withdrawal:", data);

            // Update badge
            const badge = document.getElementById("badge-penarikan");
            if (badge) {
                const current = parseInt(badge.textContent) || 0;
                const newCount = current + 1;
                badge.textContent = newCount > 9 ? "9+" : newCount;
                badge.style.display = "inline-block";
            }

            // Show browser notification
            if (Notification.permission === "granted" && document.hidden) {
                new Notification("🔔 Pengajuan Penarikan Baru", {
                    body: `${data.nama} mengajukan Rp ${new Intl.NumberFormat("id-ID").format(data.jumlah)}`,
                    icon: "/logo.png",
                });
            }
        },
    );
} else {
    console.error("❌ Pusher credentials not found");
}

// Request notification permission
if (Notification.permission === "default") {
    Notification.requestPermission();
}
