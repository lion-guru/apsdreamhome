class PropertyCard extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.shadowRoot.innerHTML = `
      <style>
        :host { 
          display: block; 
          margin: 1rem;
          font-family: var(--font-sans);
        }
        .card {
          background: white;
          border-radius: 8px;
          box-shadow: 0 2px 8px rgba(0,0,0,0.1);
          overflow: hidden;
        }
        img { width: 100%; height: 200px; object-fit: cover; }
        .content { padding: 1.5rem; }
        .price { color: var(--primary); font-weight: 600; }
      </style>
      <div class="card">
        <slot name="image"></slot>
        <div class="content">
          <h3><slot name="title">Property Title</slot></h3>
          <div class="price">₹<slot name="price">0</slot></div>
          <slot></slot>
        </div>
      </div>
    `;
  }
}

customElements.define('property-card', PropertyCard);
