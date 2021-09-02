const applicationServerKey =
    'BAwBLsFBXsZCFS_Mvc7AEwmQGQlS4gzEqwOouF2s7PWS7zXkvdoRcYQrnGpxNHccBXtVdfZqiPRtjgKe1D5Z6s8';

const pushButton = document.getElementById('pushButton');
const peopleTable = $("#peopleTable");


if (!navigator.serviceWorker) {
    window.alert('Service workers are not supported by this browser')
}

if (!window.PushManager) {
    console.warn('Push notifications are not supported by this browser');
    pushButton.style.visibility = "hidden";
}


function initServiceWorker() {
    navigator.serviceWorker.register('/serviceworker.js')
        .then(() => {
            console.log('[SW] Registration successful.');
        })
        .catch(e => {
            console.error('[SW] Registration failed: ', e);
        });
}

function initTable() {
    peopleTable.bootstrapTable({
        columns: [{
            field: "id",
            title: "ID"
        }, {
            field: "offline",
            title: ""
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


initTable();
let people = null;

let networkDataReceived = false;


if (navigator.onLine) {
    initIndexedDB();
    initServiceWorker();
    getPeopleEditIDB()
        .then(response => {
            if (response.length !== 0) {
                editPeopleSQL(response);
            }
        })
    getPeopleAddIDB()
        .then(response => {
            if (response.length !== 0) {
                addPeopleToSQL(response);
            }
        });
    getPeopleDeleteIDB()
        .then(response => {
            if (response.length !== 0) {
                deletePeopleSQL(response);
            }
        });

    people = fetch("http://localhost/people/getPeople")
        .then(response => {
            if (response.status === 401) {
                window.location.href = "http://localhost/";
            }
            return response.json()
        })
        .then(data => {
            networkDataReceived = true;
            writeToView(data);
        })
}


// fetch cached data
caches.open("dynamic-v1").then(function (cache) {
    cache.match("http://localhost/people/getPeople")
        .then(response => {
            if (!response) throw Error("No Data");
            return response.json();
        })
        .then(data => {

            if (!networkDataReceived) {


                getPeopleEditIDB().then(editPeople => {
                    for (let i = 0; i < editPeople.length; i++) {
                        for (let j = 0; j < data.length; j++) {
                            if (editPeople[i]["edit-id"] === data[j]["id"]) {
                                Object.assign(data[j],
                                    {
                                        "offline": "<i class='bi bi-pencil-fill'></i>",
                                        "fullname": editPeople[i]["edit-prename"] + " " + editPeople[i]["edit-surname"],
                                        "street": editPeople[i]["edit-street"],
                                        "address": editPeople[i]["edit-postcode"] + " " + editPeople[i]["edit-city"]
                                    });
                            }
                        }
                    }
                })
                getPeopleDeleteIDB().then(deletePeople => {
                    for (let i = 0; i < deletePeople.length; i++) {
                        for (let j = 0; j < data.length; j++) {
                            if (deletePeople[i]["delete-id"] === parseInt(data[j]["id"])) {
                                Object.assign(data[j],
                                    {
                                        "offline": "<i class='bi bi-trash-fill'></i>"
                                    });
                            }
                        }
                    }
                })
                getPeopleAddIDB().then(addPeople => {
                    if (addPeople.length !== 0) {
                        let combined = data.concat(addPeople);
                        writeToView(combined);
                    } else {
                        writeToView(data);
                    }
                })
            }

        })
        .catch(() => {
            return people
        })
        .catch(err => console.log(err));
});




function writeToView(people) {
    peopleTable.bootstrapTable('load', people);
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
/////////////////////////////////////// Indexed DB  ////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function initIndexedDB() {
    let idb = window.indexedDB.open("people", 1);
    idb.onupgradeneeded = event => {
        let db = event.target.result;
        db.createObjectStore("addPeople", {autoIncrement: true});
        db.createObjectStore("editPeople", {autoIncrement: true});
        db.createObjectStore("deletePeople", {autoIncrement: true});
    };
}

function getPeopleAddIDB() {
    return new Promise(function (resolve, reject) {
        let db = window.indexedDB.open("people", 1);
        db.onsuccess = function () {
            this.result.transaction("addPeople")
                .objectStore("addPeople").getAll().onsuccess = function (event) {
                resolve(event.target.result);
            };
        };

        db.onerror = err => {
            reject("Error in getAddIDB: ", err);
        };
    })
}


function addPeopleToSQL(people) {
    $.ajax({
        url: "http://localhost/people/addPerson_Validation",
        type: "POST",
        data: {people: people},
        success: () => {
            clearAddPeopleIDB()
                .then(() => {
                    window.alert("AddPeopleIDB cleared");
                })
                .catch(err => {
                    console.log("Error in sendPeopleToSQL: ", err);
                });
        },
        error: err => {
            console.log("Error sending data to server", err);
        }
    })
}


function clearAddPeopleIDB() {
    return new Promise((resolve, reject) => {
        let db = window.indexedDB.open("people", 1);

        db.onsuccess = function () {
            this.result.transaction("addPeople", "readwrite").objectStore("addPeople").clear();
            console.log("Cleared add people");
            resolve();
        };

        db.onerror = err => {
            reject(err);
        };
    })
}


function addToAddPeopleIDB(people) {
    return new Promise((resolve, reject) => {
        let db = window.indexedDB.open("people");

        db.onsuccess = function () {
            let objStore = this.result.transaction("addPeople", "readwrite").objectStore("addPeople");

            objStore.add(people);
            console.log("added to add people");
            resolve();
        };

        db.onerror = function (err) {
            reject(err);
        };

    })
}


function checkAddInput() {
    let numbers = /^[0-9]+$/;
    if (document.getElementById("new-prename").value === "" ||
        document.getElementById("new-surname").value === "" ||
        document.getElementById("new-street").value === "" ||
        document.getElementById("new-postcode").value === "" ||
        document.getElementById("new-postcode").value.length < 5 ||
        document.getElementById("new-postcode").value.length > 5 ||
        !document.getElementById("new-postcode").value.match(numbers) ||
        document.getElementById("new-city").value === "") {
        document.getElementById("error-new-prename").innerHTML = null;
        document.getElementById("error-new-surname").innerHTML = null;
        document.getElementById("error-new-street").innerHTML = null;
        document.getElementById("error-new-postcode").innerHTML = null;
        document.getElementById("error-new-city").innerHTML = null;
        if (document.getElementById("new-prename").value === "") {
            document.getElementById("error-new-prename").innerHTML = "<div class='alert alert-danger' role='alert'>A prename is required.</div>";
        }

        if (document.getElementById("new-surname").value === "") {
            document.getElementById("error-new-surname").innerHTML = "<div class='alert alert-danger' role='alert'>A surname is required.</div>";
        }

        if (document.getElementById("new-street").value === "") {
            document.getElementById("error-new-street").innerHTML = "<div class='alert alert-danger' role='alert'>A street is required.</div>";
        }

        if (document.getElementById("new-postcode").value.length !== 5) {
            document.getElementById("error-new-postcode").innerHTML = "<div class='alert alert-danger' role='alert'>Postcode must be of length 5.</div>";
        }

        if (!document.getElementById("new-postcode").value.match(numbers)) {
            document.getElementById("error-new-postcode").innerHTML = "<div class='alert alert-danger' role='alert'>Postcode can only consist of numbers.</div>";
        }

        if (document.getElementById("new-postcode").value === "") {
            document.getElementById("error-new-postcode").innerHTML = "<div class='alert alert-danger' role='alert'>A postcode is required.</div>";
        }

        if (document.getElementById("new-city").value === "") {
            document.getElementById("error-new-city").innerHTML = "<div class='alert alert-danger' role='alert'>A city is required.</div>";
        }

        return false;
    } else {
        return true;
    }
}

if (document.getElementById("add-button") !== null) {
    document.getElementById("add-button").addEventListener("click", async (event) => {
        event.preventDefault();

        if (checkAddInput()) {
            let person = {
                "new-prename": document.getElementById("new-prename").value,
                "new-surname": document.getElementById("new-surname").value,
                "new-street": document.getElementById("new-street").value,
                "new-postcode": document.getElementById("new-postcode").value,
                "new-city": document.getElementById("new-city").value,
            };

            document.cookie = "success=Person added.; path=/"

            if (navigator.onLine) {
                let peopleArray = new Array(person);
                addPeopleToSQL(peopleArray);
                window.location.href = "http://localhost/people";
            } else {
                Object.assign(person,
                    {
                        "offline": "<i class='bi bi-plus-lg'></i>",
                        "fullname": person["new-prename"] + " " + person["new-surname"],
                        "street": person["new-street"],
                        "address": person["new-postcode"] + " " + person["new-city"],
                    });
                addToAddPeopleIDB(person)
                    .then(() => {
                        window.location.href = "http://localhost/people";
                    })
                    .catch(error => {
                        console.log("Error adding person to addPeopleIDB", error);
                    })
            }

        }
    })
}


function getPeopleEditIDB() {
    return new Promise(function (resolve, reject) {
        let db = window.indexedDB.open("people", 1);
        db.onsuccess = function () {
            this.result.transaction("editPeople")
                .objectStore("editPeople").getAll().onsuccess = function (event) {
                resolve(event.target.result);
            };
        };

        db.onerror = err => {
            reject("Error in getEditIDB: ", err);
        };
    })
}


function editPeopleSQL(people) {
    $.ajax({
        url: "http://localhost/people/editPerson_Validation",
        type: "POST",
        data: {people: people},
        success: () => {
            clearEditPeopleIDB()
                .then(() => {
                    window.alert("EditPeopleIDB cleared");
                })
                .catch(err => {
                    console.log("Error in editPeopleSQL: ", err);
                });
        },
        error: err => {
            console.log("Error sending data to server", err);
        }
    })
}


function clearEditPeopleIDB() {
    return new Promise((resolve, reject) => {
        let db = window.indexedDB.open("people", 1);

        db.onsuccess = function () {
            this.result.transaction("editPeople", "readwrite").objectStore("editPeople").clear();
            console.log("Cleared edit people");
            resolve();
        };

        db.onerror = err => {
            reject(err);
        };
    })
}


function addToEditPeopleIDB(people) {
    return new Promise((resolve, reject) => {
        let db = window.indexedDB.open("people");

        db.onsuccess = function () {
            let objStore = this.result.transaction("editPeople", "readwrite").objectStore("editPeople");

            objStore.add(people);
            console.log("added to edit people");
            resolve();
        };

        db.onerror = function (err) {
            reject(err);
        };

    })
}


function checkEditInput() {
    let numbers = /^[0-9]+$/;
    console.log("Value: ", document.getElementById("edit-prename").value);
    if (document.getElementById("edit-prename").value === "" ||
        document.getElementById("edit-surname").value === "" ||
        document.getElementById("edit-street").value === "" ||
        document.getElementById("edit-postcode").value === "" ||
        document.getElementById("edit-postcode").value.length < 5 ||
        document.getElementById("edit-postcode").value.length > 5 ||
        !document.getElementById("edit-postcode").value.match(numbers) ||
        document.getElementById("edit-city").value === "") {
        document.getElementById("error-edit-prename").innerHTML = null;
        document.getElementById("error-edit-surname").innerHTML = null;
        document.getElementById("error-edit-street").innerHTML = null;
        document.getElementById("error-edit-postcode").innerHTML = null;
        document.getElementById("error-edit-city").innerHTML = null;
        if (document.getElementById("edit-prename").value === "") {
            document.getElementById("error-edit-prename").innerHTML = "<div class='alert alert-danger' role='alert'>A prename is required.</div>";
        }

        if (document.getElementById("edit-surname").value === "") {
            document.getElementById("error-edit-surname").innerHTML = "<div class='alert alert-danger' role='alert'>A surname is required.</div>";
        }

        if (document.getElementById("edit-street").value === "") {
            document.getElementById("error-edit-street").innerHTML = "<div class='alert alert-danger' role='alert'>A street is required.</div>";
        }

        if (document.getElementById("edit-postcode").value.length !== 5) {
            document.getElementById("error-edit-postcode").innerHTML = "<div class='alert alert-danger' role='alert'>Postcode must be of length 5.</div>";
        }

        if (!document.getElementById("edit-postcode").value.match(numbers)) {
            document.getElementById("error-edit-postcode").innerHTML = "<div class='alert alert-danger' role='alert'>Postcode can only consist of numbers.</div>";
        }

        if (document.getElementById("edit-postcode").value === "") {
            document.getElementById("error-edit-postcode").innerHTML = "<div class='alert alert-danger' role='alert'>A postcode is required.</div>";
        }

        if (document.getElementById("edit-city").value === "") {
            document.getElementById("error-edit-city").innerHTML = "<div class='alert alert-danger' role='alert'>A city is required.</div>";
        }

        return false;
    } else {
        return true;
    }
}

if (document.getElementById("edit-button") !== null) {
    document.getElementById("edit-button").addEventListener("click", async (event) => {
        event.preventDefault();

        if (checkEditInput()) {
            let person = {
                "edit-id": document.getElementById("edit-id").value,
                "edit-prename": document.getElementById("edit-prename").value,
                "edit-surname": document.getElementById("edit-surname").value,
                "edit-street": document.getElementById("edit-street").value,
                "edit-postcode": document.getElementById("edit-postcode").value,
                "edit-city": document.getElementById("edit-city").value
            };

            document.cookie = "success=Person updated.; path=/"

            if (navigator.onLine) {
                let peopleArray = new Array(person);
                editPeopleSQL(peopleArray);
                window.location.href = "http://localhost/people";
            } else {
                Object.assign(person,
                    {
                        "offline": "<i class='bi bi-plus'></i>",
                    });
                addToEditPeopleIDB(person)
                    .then(() => {
                        window.location.href = "http://localhost/people";
                    })
                    .catch(error => {
                        console.log("Error adding person to editPeopleIDB", error);
                    })
            }

        }
    })
}


function getPeopleDeleteIDB() {
    return new Promise(function (resolve, reject) {
        let db = window.indexedDB.open("people", 1);
        db.onsuccess = function () {
            this.result.transaction("deletePeople")
                .objectStore("deletePeople").getAll().onsuccess = function (event) {
                resolve(event.target.result);
            };
        };

        db.onerror = err => {
            reject("Error in getPeopleDeleteIDB: ", err);
        };
    })
}


function deletePeopleSQL(people) {
    $.ajax({
        url: "http://localhost/people/deletePerson",
        type: "POST",
        data: {people: people},
        success: () => {
            clearDeletePeopleIDB()
                .then(() => {
                    window.alert("DeletePeopleIDB cleared");
                })
                .catch(err => {
                    console.log("Error in deletePeopleSQL: ", err);
                });
        },
        error: err => {
            console.log("Error sending data to server", err);
        }
    })
}


function clearDeletePeopleIDB() {
    return new Promise((resolve, reject) => {
        let db = window.indexedDB.open("people", 1);

        db.onsuccess = function () {
            this.result.transaction("deletePeople", "readwrite").objectStore("deletePeople").clear();
            console.log("Cleared delete people");
            resolve();
        };

        db.onerror = err => {
            reject(err);
        };
    })
}


function addToDeletePeopleIDB(people) {
    return new Promise((resolve, reject) => {
        let db = window.indexedDB.open("people");

        db.onsuccess = function () {
            let objStore = this.result.transaction("deletePeople", "readwrite").objectStore("deletePeople");

            objStore.add(people);
            console.log("added to delete people");
            resolve();
        };

        db.onerror = function (err) {
            reject(err);
        };

    })
}

function deletePerson(id) {
    if (confirm("Are you sure you want to remove the person with id " + id + " ?")) {
        let person = {
            "delete-id": id
        };

        document.cookie = "success=Person deleted.; path=/"

        if (navigator.onLine) {
            let peopleArray = new Array(person);
            deletePeopleSQL(peopleArray);
            window.location.href = "http://localhost/people";
        } else {
            addToDeletePeopleIDB(person)
                .then(() => {
                    window.location.href = "http://localhost/people";
                })
                .catch(error => {
                    console.log("Error adding person to deletePeopleIDB", error);
                })
        }
    }
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////// Push Notifications ///////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (pushButton !== null) {
    navigator.serviceWorker.ready
        .then(serviceWorkerRegistration =>
            serviceWorkerRegistration.pushManager.getSubscription())
        .then(subscription => {
            if (subscription === null) {
                pushButton.textContent = 'Push off';
            } else {
                pushButton.textContent = 'Push on';
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
                    pushButton.textContent = 'Push on';
                } else {
                    if (confirm("are you sure you want to unsubscribe?")) {
                        push_unsubscribe();
                        pushButton.textContent = 'Push off';
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
            subscription.unsubscribe();
            return push_sendSubscriptionToServer(subscription, 'DELETE');
        })
        .catch(e => {
            console.error('Error when unsubscribing the user', e);
        });
}

function push_sendSubscriptionToServer(subscription, method) {
    const key = subscription.getKey('p256dh');
    const token = subscription.getKey('auth');

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
            "authToken": token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null
        }),
    });
}

