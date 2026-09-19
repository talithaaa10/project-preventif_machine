const Chart = typeof window !== 'undefined' ? window.Chart : null;

export default function () {
  if (typeof Alpine === 'undefined') return;

  Alpine.data('chartjs', () => ({
    config: {},
    _chart: null,
    init() {
      try {
        const raw = this.$el.getAttribute('data-config') || '{}';
        this.config = JSON.parse(raw);
      } catch (e) {
        this.config = {};
      }

      // If element is a canvas, get its context
      const el = this.$el;
      let ctx = null;
      if (el.tagName.toLowerCase() === 'canvas') {
        ctx = el.getContext('2d');
      } else {
        // find canvas inside
        const canvas = el.querySelector('canvas');
        if (canvas) ctx = canvas.getContext('2d');
      }

      if (!ctx) return;

      this._chart = new Chart(ctx, this.config);
    },
    destroy() {
      if (this._chart) {
        this._chart.destroy();
        this._chart = null;
      }
    }
  }));
}
