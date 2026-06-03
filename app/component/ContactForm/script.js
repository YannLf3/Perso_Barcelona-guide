/**
 * ContactForm — formulaire de contact redesigné
 */

import { escapeHtml } from "../../utils/escapeHtml.js";

// Chargement du template HTML externe
const template = await (
  await fetch(new URL("./template.html", import.meta.url))
).text();

export const ContactForm = {
  /**
   * @param {string} handlerSubmit
   * @param {string} lang
   * @returns {string} HTML du formulaire
   */

  format(handlerSubmit, lang = "en") {
    const labels = {
      en: {
        eyebrow: "GET IN TOUCH",
        title:
          "Plan your <span class='contact-form__title-accent'>perfect tour</span>",
        subtitle:
          "Have a question or want to book a custom experience? Fill in the form and we'll get back to you within 24 hours.",
        namePlaceholder: "Your name",
        emailPlaceholder: "Your email address",
        messagePlaceholder: "Tell us about your dream Barcelona experience…",
        submit: "Send message",
        or: "Or reach us directly at",
        email: "barcelona@guide.es",
        nameLabel: "Full name",
        emailLabel: "Email",
        messageLabel: "Your message",
      },
      fr: {
        eyebrow: "CONTACTEZ-NOUS",
        title:
          "Planifiez votre <span class='contact-form__title-accent'>visite idéale</span>",
        subtitle:
          "Une question ou envie de réserver une expérience sur mesure ? Remplissez le formulaire, nous vous répondons sous 24h.",
        namePlaceholder: "Votre nom",
        emailPlaceholder: "Votre adresse email",
        messagePlaceholder: "Décrivez votre expérience barcelonaise idéale…",
        submit: "Envoyer le message",
        or: "Ou contactez-nous directement à",
        email: "barcelona@guide.es",
        nameLabel: "Nom complet",
        emailLabel: "Email",
        messageLabel: "Votre message",
      },
      es: {
        eyebrow: "CONTÁCTANOS",
        title:
          "Planifica tu <span class='contact-form__title-accent'>tour perfecto</span>",
        subtitle:
          "¿Tienes alguna pregunta o quieres reservar una experiencia a medida? Rellena el formulario y te responderemos en 24 horas.",
        namePlaceholder: "Tu nombre",
        emailPlaceholder: "Tu dirección de email",
        messagePlaceholder: "Cuéntanos tu experiencia soñada en Barcelona…",
        submit: "Enviar mensaje",
        or: "O contáctanos directamente en",
        email: "barcelona@guide.es",
        nameLabel: "Nombre completo",
        emailLabel: "Email",
        messageLabel: "Tu mensaje",
      },
      it: {
        eyebrow: "CONTATTACI",
        title:
          "Pianifica il tuo <span class='contact-form__title-accent'>tour perfetto</span>",
        subtitle:
          "Hai domande o vuoi prenotare un'esperienza su misura? Compila il modulo e ti risponderemo entro 24 ore.",
        namePlaceholder: "Il tuo nome",
        emailPlaceholder: "Il tuo indirizzo email",
        messagePlaceholder: "Descrivici la tua esperienza barcellonese ideale…",
        submit: "Invia messaggio",
        or: "O contattaci direttamente a",
        email: "barcelona@guide.es",
        nameLabel: "Nome completo",
        emailLabel: "Email",
        messageLabel: "Il tuo messaggio",
      },
    };

    const l = labels[lang] || labels.en;

    // On utilise .replaceAll() pour injecter les données de langue et les fonctions JS.
    return template
      .replaceAll("{{eyebrow}}", escapeHtml(l.eyebrow))
      .replaceAll("{{title}}", l.title) // Contient du HTML (span), on n'escape pas
      .replaceAll("{{subtitle}}", escapeHtml(l.subtitle))
      .replaceAll("{{orLabel}}", escapeHtml(l.or))
      .replaceAll("{{email}}", escapeHtml(l.email))
      .replaceAll("{{handlerSubmit}}", handlerSubmit)
      .replaceAll("{{nameLabel}}", escapeHtml(l.nameLabel))
      .replaceAll("{{namePlaceholder}}", escapeHtml(l.namePlaceholder))
      .replaceAll("{{emailLabel}}", escapeHtml(l.emailLabel))
      .replaceAll("{{emailPlaceholder}}", escapeHtml(l.emailPlaceholder))
      .replaceAll("{{messageLabel}}", escapeHtml(l.messageLabel))
      .replaceAll("{{messagePlaceholder}}", escapeHtml(l.messagePlaceholder))
      .replaceAll("{{submitLabel}}", escapeHtml(l.submit));
  },
};
