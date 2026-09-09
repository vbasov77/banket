importScripts(
    "https://www.gstatic.com/firebasejs/12.18.0/firebase-app-compat.js",
    "https://www.gstatic.com/firebasejs/12.18.0/firebase-messaging-compat.js"
);

const firebaseConfig = {
    apiKey: "AIzaSyARz2ukBnphzY8PPzBZkvf1tHXEB4oEACw",
    authDomain: "feast-boom.firebaseapp.com",
    projectId: "feast-boom",
    storageBucket: "feast-boom.firebasestorage.app",
    messagingSenderId: "1016746447319",
    appId: "1:1016746447319:web:1e10399fee5c251f6efb92"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging(); // ← ВОЗВРАЩАЕМ — нужно для onMessage

// Свой обработчик — показывает уведомление только для 111 и только если вкладка свёрнута
self.addEventListener("push", async (event) => {
    const payload = event.data ? event.data.json() : {};
    const data = payload.data || {};

    // Служебные коды — молча
    if (String(data.code) === "222" || String(data.code) === "333") {
        return;
    }

    // Вкладка видна? Не показываем уведомление — onMessage в странице обновит UI
    const allClients = await self.clients.matchAll({ type: 'window' });
    const isVisible = allClients.some(client => client.visibilityState === 'visible');
    if (isVisible) {
        return;
    }

    // Вкладка свёрнута/закрыта — показываем уведомление
    event.waitUntil(
        self.registration.showNotification(
            data.title || "Новое сообщение",
            {
                body: data.body || "",
                icon: "/icons/fb.svg",
                badge: "/icons/fb.svg",
                data: data
            }
        )
    );
});

self.addEventListener("notificationclick", (event) => {
    event.notification.close();
    event.waitUntil(clients.openWindow("/"));
});
