self.addEventListener("push", function (event) {
  const data = event.data.json();
  self.registration.showNotification(data.title, {
    body: data.body,
    icon: "assets/images/favicon.png"
  });
});

self.addEventListener("notificationclick", function (event) {
  event.notification.close();
  event.waitUntil(clients.openWindow("/"));
});
//Private Key
// klZ8EglEozIcBRQ4Y_QesIZdkXh-HVtkENsy1DeenfI
// Public Key
// BEwUXj9WEZ4MLzQIcjaurlatlcF_N-3egXKxdOnw6xCCXQtrDaAm6yJJeRj8yiYzBtD-F8J45C6fhEn0tiLt9eU
