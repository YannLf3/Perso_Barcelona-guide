const initGlow = () => {
  const glow = document.querySelector(".cursor-glow");
  if (!glow) return;
  let isMoving = false;

  window.addEventListener("mousemove", (e) => {
    // On évite de surcharger le navigateur si l'écran n'a pas encore été rafraîchi
    if (!isMoving) {
      window.requestAnimationFrame(() => {
        // On utilise translate3d pour profiter de l'accélération matérielle et éviter les saccades.
        glow.style.transform = `translate3d(${e.clientX}px, ${e.clientY}px, 0)`;
        isMoving = false;
      });
      isMoving = true;
    }
  });
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initGlow);
} else {
  initGlow();
}

// ce fihcier permet de gérer la animation du glow qui suit le curseur, en utilisant une technique de "throttling" avec requestAnimationFrame pour éviter les saccades et améliorer les performances.
