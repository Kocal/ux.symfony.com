import { Controller } from "@hotwired/stimulus";

// Survives Turbo replacing the results frame.
const favorites = new Set();

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ["favorite"];
  static values = { id: Number, favorite: Boolean };

  connect() {
    this.favoriteValue = favorites.has(this.idValue);
  }

  toggleFavorite() {
    this.favoriteValue = !this.favoriteValue;
    if (this.favoriteValue) favorites.add(this.idValue);
    else favorites.delete(this.idValue);
    this.dispatch("change", { detail: { product: this.idValue, favorite: this.favoriteValue } });
  }

  favoriteValueChanged(value) {
    this.favoriteTarget.setAttribute("aria-pressed", String(value));
  }

  remove() {
    this.dispatch("removed", { detail: { product: this.idValue } });
  }
}
