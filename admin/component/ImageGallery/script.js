export const ImageGallery = {
  format(images, handlerUpload) {
    const safeImages = Array.isArray(images) ? images : [];

    const imageItems = safeImages
      .map(
        (fileName) => `
          <figure class="image-gallery__item">
            <img
              src="../Images/${encodeURIComponent(fileName)}"
              alt="${fileName}"
              loading="lazy"
            />
            <figcaption>${fileName}</figcaption>
          </figure>
        `,
      )
      .join("");

    return `
      <section class="admin-block">
        <h2>Image gallery</h2>

        <form
          class="image-upload-form"
          onsubmit="${handlerUpload}; return false;"
        >
          <label>
            Upload an image
            <input
              type="file"
              name="image"
              accept="image/jpeg,image/png,image/webp"
              required
            />
          </label>

          <button type="submit">Upload image</button>
        </form>

        <div class="image-gallery" data-image-gallery>
          ${imageItems}
        </div>
      </section>
    `;
  },

  bindUpload(handlerUpload) {
    const form = document.querySelector(".image-upload-form");

    if (!form) {
      return;
    }

    form.addEventListener("submit", handlerUpload);
  },
};
