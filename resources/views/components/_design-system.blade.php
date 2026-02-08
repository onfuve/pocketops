{{-- Unified design system for pocket-business. Include in layout. --}}
@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════════
   DESIGN SYSTEM — pocket-business
   Font: Vazirmatn | RTL | Persian
   ═══════════════════════════════════════════════════════════════════ */

:root {
  --ds-font: 'Vazirmatn', sans-serif;
  --ds-radius-sm: 0.5rem;
  --ds-radius: 0.75rem;
  --ds-radius-lg: 1rem;
  /* Colors */
  --ds-primary: #059669;
  --ds-primary-hover: #047857;
  --ds-primary-dark: #065f46;
  --ds-primary-bg: #ecfdf5;
  --ds-primary-border: #a7f3d0;
  /* Neutral */
  --ds-text: #292524;
  --ds-text-muted: #57534e;
  --ds-text-subtle: #78716c;
  --ds-text-faint: #a8a29e;
  --ds-border: #e7e5e4;
  --ds-border-hover: #d6d3d1;
  --ds-bg: #fff;
  --ds-bg-subtle: #f5f5f4;
  --ds-bg-muted: #fafaf9;
  /* Semantic */
  --ds-success: #047857;
  --ds-success-bg: #d1fae5;
  --ds-warning: #b45309;
  --ds-warning-bg: #fef3c7;
  --ds-danger: #b91c1c;
  --ds-danger-bg: #fef2f2;
  --ds-danger-border: #fecaca;
  /* Shadow */
  --ds-shadow: 0 1px 3px rgba(0,0,0,0.04);
  --ds-shadow-hover: 0 4px 12px rgba(0,0,0,0.08);
}

/* ─── Page layout ─── */
.ds-page { max-width: 52rem; margin: 0 auto; padding: 0 1rem; box-sizing: border-box; font-family: var(--ds-font); }
.ds-page-header { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
.ds-page-title { display: flex; align-items: center; gap: 0.75rem; margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 700; color: var(--ds-text); }
.ds-page-title-icon { display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: var(--ds-radius); background: var(--ds-primary-bg); color: var(--ds-primary); border: 2px solid var(--ds-primary-border); }
.ds-page-subtitle { margin: 0.25rem 0 0 0; font-size: 0.875rem; color: var(--ds-text-subtle); }

/* ─── Buttons ─── */
.ds-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; min-height: 42px; border-radius: var(--ds-radius-sm); font-size: 0.875rem; font-weight: 600; font-family: var(--ds-font); text-decoration: none; cursor: pointer; border: 2px solid transparent; transition: all 0.2s; }
.ds-btn-primary, .ds-btn.ds-btn-primary { background: var(--ds-primary); color: #fff; border-color: var(--ds-primary-hover); box-shadow: 0 2px 4px rgba(5,150,105,0.25); }
.ds-btn-primary:hover, .ds-btn.ds-btn-primary:hover { background: var(--ds-primary-hover); box-shadow: 0 4px 8px rgba(5,150,105,0.3); transform: translateY(-1px); }
.ds-btn-secondary, .ds-btn.ds-btn-secondary { background: var(--ds-bg); color: var(--ds-text-muted); border-color: var(--ds-border); }
.ds-btn-secondary:hover, .ds-btn.ds-btn-secondary:hover { background: var(--ds-bg-muted); border-color: var(--ds-border-hover); color: var(--ds-text); }
.ds-btn-outline { background: var(--ds-bg); color: var(--ds-text-muted); border-color: var(--ds-border); font-weight: 500; }
.ds-btn-outline:hover { background: var(--ds-bg-muted); border-color: var(--ds-border-hover); }
.ds-btn-ghost { background: var(--ds-primary-bg); color: var(--ds-primary); border-color: var(--ds-primary-border); }
.ds-btn-ghost:hover { background: var(--ds-success-bg); border-color: #6ee7b7; }
.ds-btn-danger { background: var(--ds-bg); color: var(--ds-danger); border-color: var(--ds-danger-border); font-weight: 500; }
.ds-btn-danger:hover { background: var(--ds-danger-bg); }
.ds-btn-dashed { border-style: dashed; border-color: var(--ds-border-hover); }
.ds-btn-dashed:hover { border-color: var(--ds-primary); color: var(--ds-primary); background: var(--ds-primary-bg); }

/* ─── Filter tabs (selectable options) ─── */
.ds-filter-tabs { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; padding: 0.25rem; border-radius: var(--ds-radius); background: var(--ds-bg); border: 2px solid var(--ds-border); box-shadow: var(--ds-shadow); margin-bottom: 1.5rem; }
.ds-filter-tabs a { display: inline-flex; align-items: center; padding: 0.5rem 1rem; border-radius: var(--ds-radius-sm); font-size: 0.875rem; font-weight: 500; text-decoration: none; min-height: 44px; color: var(--ds-text-muted); transition: all 0.2s; font-family: var(--ds-font); }
.ds-filter-tabs a:not(.ds-filter-active):hover { background: var(--ds-bg-subtle); }
.ds-filter-tabs a.ds-filter-active { box-shadow: 0 1px 2px rgba(0,0,0,0.1); }

/* ─── Inputs ─── */
.ds-input, .ds-select, .ds-textarea { width: 100%; padding: 0.625rem 0.75rem; border: 2px solid var(--ds-border-hover); border-radius: var(--ds-radius-sm); font-size: 1rem; color: var(--ds-text); background: var(--ds-bg); font-family: var(--ds-font); box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s; }
.ds-input:focus, .ds-select:focus, .ds-textarea:focus { outline: none; border-color: var(--ds-primary); box-shadow: 0 0 0 3px rgba(5,150,105,0.15); }
.ds-textarea { resize: vertical; min-height: 100px; }
.ds-label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--ds-text-muted); margin-bottom: 0.25rem; }
.ds-select { min-height: 44px; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2378716c'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: left 0.5rem center; background-size: 1.25rem; padding-left: 2rem; }

/* ─── Cards ─── */
.ds-card { background: var(--ds-bg); border: 2px solid var(--ds-border); border-radius: var(--ds-radius-lg); padding: 1.25rem; margin-bottom: 0.75rem; box-shadow: var(--ds-shadow); transition: all 0.2s; text-decoration: none; color: inherit; display: block; }
.ds-card:hover { border-color: var(--ds-border-hover); box-shadow: var(--ds-shadow-hover); transform: translateY(-1px); }
.ds-card-static { text-decoration: none; cursor: default; }
.ds-card-static:hover { transform: none; }

/* ─── Badges ─── */
.ds-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
.ds-badge-primary { background: var(--ds-success-bg); color: var(--ds-success); }
.ds-badge-amber { background: var(--ds-warning-bg); color: var(--ds-warning); }

/* ─── Form card (section) ─── */
.ds-form-card { background: var(--ds-bg); border: 2px solid var(--ds-border); border-radius: var(--ds-radius-lg); padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: var(--ds-shadow); }
.ds-form-card-title { font-size: 1rem; font-weight: 600; color: var(--ds-text); margin: 0 0 1rem 0; padding-bottom: 0.75rem; border-bottom: 2px solid var(--ds-bg-subtle); }

/* ─── Empty state ─── */
.ds-empty { padding: 2.5rem; text-align: center; border-radius: var(--ds-radius-lg); border: 2px dashed var(--ds-border); background: var(--ds-bg-muted); }

/* ─── Search row ─── */
.ds-search-row { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
.ds-search-form { display: flex; gap: 0.5rem; flex: 1; min-width: 0; max-width: 28rem; }
.ds-search-form input { flex: 1; min-width: 0; }

/* ─── Chip / tag (selectable) ─── */
.ds-chip { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; border-radius: var(--ds-radius-sm); border: 2px solid var(--ds-border); background: var(--ds-bg); cursor: pointer; transition: all 0.2s; min-height: 44px; font-family: var(--ds-font); }
.ds-chip:hover { border-color: var(--ds-border-hover); background: var(--ds-bg-muted); }

/* ─── Alerts ─── */
.ds-alert-success { padding: 0.75rem 1rem; border-radius: var(--ds-radius); background: var(--ds-primary-bg); border: 2px solid var(--ds-primary-border); color: var(--ds-primary-dark); font-size: 0.875rem; }
.ds-alert-warning { padding: 0.75rem 1rem; border-radius: var(--ds-radius); background: #fef3c7; border: 2px solid #fcd34d; color: #92400e; font-size: 0.875rem; }
.ds-alert-error { padding: 0.75rem 1rem; border-radius: var(--ds-radius); background: var(--ds-danger-bg); border: 2px solid var(--ds-danger-border); color: var(--ds-danger); font-size: 0.875rem; }
</style>
@endpush
