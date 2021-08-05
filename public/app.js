const applicationServerKey =
    'BAwBLsFBXsZCFS_Mvc7AEwmQGQlS4gzEqwOouF2s7PWS7zXkvdoRcYQrnGpxNHccBXtVdfZqiPRtjgKe1D5Z6s8';

const pushButton = document.getElementById('pushButton');
const peopleTable = $("#peopleTable");


if (!navigator.serviceWorker) {
    console.warn('Service workers are not supported by this browser');
}

if (!window.PushManager) {
    console.warn('Push notifications are not supported by this browser');
}

if (!ServiceWorkerRegistration.prototype.showNotification) {
    console.warn('Notifications are not supported by this browser');
}

if (Notification.permission === 'denied') {
    console.warn('Notifications are denied by the user');
}

function initServiceWorker() {
    navigator.serviceWorker.register('serviceworker.js')
        .then(() => {
                // console.log('[SW] Service worker has been registered');
                return navigator.serviceWorker.ready;
            },
            e => {
                console.error('[SW] Service worker registration failed', e);
            }
        );
}

function initTable() {
    peopleTable.bootstrapTable({
        columns: [{
            field: "id",
            title: "ID"
        }, {
            field: "fullname",
            title: "Name"
        }, {
            field: "street",
            title: "Street"
        }, {
            field: "address",
            title: "City"
        }, {
            field: "created_by",
            title: "Created"
        }, {
            field: "edited_by",
            title: "Edited"
        }, {
            field: "buttons",
            title: "",
            class: "text-center"
        }]
    })
}

let people = null;

if (navigator.onLine) {
    people = fetch("http://localhost/getPeople")
        .then(response => response.json())
        .then(data => {
            writeToView(data);
        })
}

// fetch cached data
caches.open("dynamic-v1").then(function (cache) {
    cache.match("http://localhost/getPeople")
        .then(response => {
            if (!response) throw Error("No Data");
            return response.json();
        })
        .then(data => {
            writeToView(data);
        })
        .catch(() => people)
});


function writeToView(people) {
    peopleTable.bootstrapTable('load', people);
}


// div to show if internet connection exists
if (navigator.onLine) {
    document.getElementById("show-online").classList.remove("bg-danger");
    document.getElementById("show-online").classList.add("bg-success");
    document.getElementById("show-online").innerText = "Online";
} else {
    document.getElementById("show-online").classList.remove("bg-success");
    document.getElementById("show-online").classList.add("bg-danger");
    document.getElementById("show-online").innerText = "Offline";
}

// reloads page when back online
window.addEventListener('online', () => {
    location.reload();
})

// reloads page when offline
window.addEventListener('offline', () => {
    location.reload();
})

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////// Push Notifications ///////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (pushButton !== null) {
    navigator.serviceWorker.ready
        .then(serviceWorkerRegistration =>
            serviceWorkerRegistration.pushManager.getSubscription())
        .then(subscription => {
            // console.log("Subscription: " + JSON.stringify(subscription));
            if (subscription === null) {
                pushButton.textContent = 'Allow Push';
            } else {
                pushButton.textContent = 'Stop Push';
            }
        });


    pushButton.addEventListener('click', () => {
        navigator.serviceWorker.ready
            .then(serviceWorkerRegistration =>
                serviceWorkerRegistration.pushManager.getSubscription())
            .then(subscription => {
                if (subscription === null) {
                    push_subscribe()
                        .then(res => {
                            console.log("subscribe: " + res);
                        });
                    pushButton.textContent = 'Stop Push';
                } else {
                    if (confirm("are you sure you want to unsubscribe?")) {
                        push_unsubscribe();
                        pushButton.textContent = 'Allow Push'
                    }
                }
            });
    });
}


function checkNotificationPermission() {
    return new Promise((resolve, reject) => {
        if (Notification.permission === 'denied') {
            reject(new Error('Push messages are blocked.'));
        } else if (Notification.permission === 'granted') {
            resolve();
        } else {
            Notification.requestPermission().then(result => {
                if (result !== 'granted') {
                    reject(new Error('Bad permission result'));
                } else {
                    resolve();
                }
            });
        }
    })
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function push_subscribe() {
    return checkNotificationPermission()
        .then(() => navigator.serviceWorker.ready)
        .then(serviceWorkerRegistration =>
            serviceWorkerRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(applicationServerKey)
            })
        )
        .then(subscription => {
            return push_sendSubscriptionToServer(subscription, 'POST');
        })
        .then(subscription => {
            isPushEnabled = true;
        })
        .catch(e => {
            if (Notification.permission === 'denied') {
                console.warn('Notifications are denied by the user.');
            } else {
                console.error('Impossible to subscribe to push notifications', e);
            }
        });
}

function push_unsubscribe() {
    navigator.serviceWorker.ready
        .then(serviceWorkerRegistration =>
            serviceWorkerRegistration.pushManager.getSubscription())
        .then(subscription => {
            if (!subscription) {
                return;
            }
            return push_sendSubscriptionToServer(subscription, 'DELETE');
        })
        .then(subscription => {
            subscription.unsubscribe();
            isPushEnabled = false;
        })
        .catch(e => {
            console.error('Error when unsubscribing the user', e);
        });
}

function push_sendSubscriptionToServer(subscription, method) {
    const key = subscription.getKey('p256dh');
    const token = subscription.getKey('auth');
    const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

    return fetch('http://localhost/people/push_subscription', {
        method,
        mode: "same-origin",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            "endpoint": subscription.endpoint,
            "publicKey": key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
            "authToken": token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
            contentEncoding
        }),
    }).then(() => subscription);
}

initTable();
initServiceWorker();
// https://developer.mozilla.org/en-US/docs/Web/API/PushManager/getSubscription