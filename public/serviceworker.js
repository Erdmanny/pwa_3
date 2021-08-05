const STATIC_CACHE = "static-v1";
const DYNAMIC_CACHE = "dynamic-v1";

const DYNAMIC_ASSETS = [
    "getPeople"
];


const STATIC_ASSETS = [
    "logo.ico",
    "manifest.webmanifest",
    "icon/icon96.png",
    "icon/icon144.png",
    "https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css",
    "https://unpkg.com/bootstrap-table@1.18.0/dist/bootstrap-table.min.css",
    "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css",
    "https://code.jquery.com/jquery-3.6.0.min.js",
    "https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js",
    "https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js",
    "https://unpkg.com/bootstrap-table@1.18.0/dist/bootstrap-table.min.js",
    "https://unpkg.com/bootstrap-table@1.18.1/dist/extensions/mobile/bootstrap-table-mobile.min.js",
    "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/fonts/bootstrap-icons.woff?856008caa5eb66df68595e734e59580d",
    "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/fonts/bootstrap-icons.woff2?856008caa5eb66df68595e734e59580d",
    "/",
    "people",
    "addPerson",
    "editPerson",
    "app.js"
];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then(cache => {
            cache.addAll(STATIC_ASSETS)
                .catch(err => {
                    console.log(err);
                });
        })
    );
    event.waitUntil(
        caches.open(DYNAMIC_CACHE).then(cache => {
            cache.addAll(DYNAMIC_ASSETS);
        })
    );
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(keys
                .filter(key => key !== STATIC_CACHE && key !== DYNAMIC_CACHE)
                .map(key => caches.delete(key))
            )
        })
    );
});



self.addEventListener("fetch", (event) => {
    if (event.request.url.endsWith("getPeople")) {
        event.respondWith(
            caches.open(DYNAMIC_CACHE).then(function (cache) {
                return fetch(event.request).then(function (response) {
                    cache.put(event.request, response.clone())
                        .catch(error => {});
                    return response;
                })
            })
        )
    } else if (event.request.url.includes("editPerson?")){
        event.respondWith(
            caches.match("editPerson").then(cacheRes => {
                return cacheRes || fetch(event.request)
            })
        )
    } else {
        event.respondWith(
            caches.match(event.request).then(cacheRes => {
                return cacheRes || fetch(event.request)
            })
        )
    }
});



self.addEventListener('push', event => {
    if (!(self.Notification && self.Notification.permission === 'granted')){
        return;
    }

    const sendNotification = (body) => {
        var data = JSON.parse(body);

        const title = data.title;

        return self.registration.showNotification(title, {
            body: data.body,
            icon: data.icon,
            badge: data.badge,
            image: data.image,
            data: {
                url: data.url
            }
        });
    };

    if (event.data) {
        const message = event.data.text();
        event.waitUntil(sendNotification(message));
    }
})

self.addEventListener('notificationclick', event => {
    console.log(('[Service Worker] Notification click received.'));
    event.notification.close();

    const url = event.notification.data.url;
    console.log(url);

    event.waitUntil(clients.matchAll({
        type: 'window'
    }).then(clientList => {
        console.log(clientList);
        for (let i = 0; i < clientList.length; i++) {
            let client = clientList[i];
            if (client.url === self.registration.scope && 'focus' in client) {
                return client.focus();
            }
        }
        if (clients.openWindow){
            return clients.openWindow(url);
        }
    }));
})