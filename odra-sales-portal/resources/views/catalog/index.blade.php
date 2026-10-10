@extends(auth()->check() && auth()->user()->isManager() && request()->routeIs('manager.*') ? 'layouts.manager' : 'layouts.app')

@section('title', 'Sweeper Models & Performance Showcase')
@section('header-title', 'ODRA Sweeper Models Showcase')

@push('styles')
<style>
/* Mercedes-Benz AMG-Inspired Automotive Luxury Theme */
:root {
  --amg-dark: #0a0e17;
  --amg-card: #111827;
  --amg-card-hover: #162032;
  --amg-border: #1e293b;
  --amg-border-highlight: #334155;
  --amg-text-primary: #f8fafc;
  --amg-text-secondary: #94a3b8;
  --amg-text-muted: #64748b;
  --odra-red: #ac2529;
  --odra-red-bright: #dc2626;
  --odra-red-glow: rgba(172, 37, 41, 0.4);
  --silver-accent: #e2e8f0;
}

.catalog-container {
  max-width: 1440px;
  margin: 0 auto;
  padding: 0 0 48px;
  color: var(--amg-text-primary);
}

/* Hero Banner */
.amg-hero {
  position: relative;
  background: radial-gradient(circle at 50% 0%, #1e293b 0%, #0a0e17 80%);
  border-radius: 16px;
  padding: 48px 40px 40px;
  margin-bottom: 32px;
  border: 1px solid var(--amg-border);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
  overflow: hidden;
}
.amg-hero::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, transparent, var(--odra-red), transparent);
}
.hero-tagline {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: var(--odra-red-bright);
  background: rgba(172, 37, 41, 0.12);
  border: 1px solid rgba(172, 37, 41, 0.3);
  padding: 6px 14px;
  border-radius: 999px;
  margin-bottom: 16px;
}
.amg-hero h1 {
  font-size: 38px;
  font-weight: 800;
  letter-spacing: -0.5px;
  margin: 0 0 12px;
  line-height: 1.15;
  background: linear-gradient(180deg, #ffffff 0%, #cbd5e1 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.amg-hero p.hero-sub {
  font-size: 16px;
  color: var(--amg-text-secondary);
  max-width: 780px;
  margin: 0 0 28px;
  line-height: 1.6;
}
.hero-highlights-strip {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 24px;
}
.hero-stat {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  color: #cbd5e1;
}
.hero-stat-icon {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(172, 37, 41, 0.2);
  color: #fca5a5;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: bold;
}

/* Category Filter Bar (Mercedes AMG Pill Navigation) */
.filter-bar-wrap {
  position: sticky;
  top: 72px;
  z-index: 40;
  background: rgba(10, 14, 23, 0.92);
  backdrop-filter: blur(12px);
  padding: 16px 20px;
  border-radius: 12px;
  border: 1px solid var(--amg-border);
  margin-bottom: 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
}
.filter-pills {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.filter-pill {
  background: rgba(255, 255, 255, 0.05);
  color: var(--amg-text-secondary);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 8px 16px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
}
.filter-pill:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border-color: rgba(255, 255, 255, 0.2);
}
.filter-pill.active {
  background: var(--odra-red);
  color: white;
  border-color: var(--odra-red-bright);
  box-shadow: 0 0 15px var(--odra-red-glow);
}
.filter-search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
  padding: 6px 14px;
  border-radius: 999px;
}
.filter-search-box input {
  background: transparent;
  border: none;
  outline: none;
  color: white;
  font-size: 13px;
  width: 200px;
}
.filter-search-box input::placeholder {
  color: var(--amg-text-muted);
}

/* Models Grid */
.models-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(560px, 1fr));
  gap: 32px;
  margin-bottom: 48px;
}
@media (max-width: 1200px) {
  .models-grid {
    grid-template-columns: 1fr;
  }
}

/* Mercedes-AMG Model Card */
.model-card {
  background: var(--amg-card);
  border: 1px solid var(--amg-border);
  border-radius: 16px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  position: relative;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
}
.model-card:hover {
  transform: translateY(-4px);
  border-color: var(--amg-border-highlight);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 25px rgba(172, 37, 41, 0.15);
}
.model-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, var(--odra-red), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
}
.model-card:hover::after {
  opacity: 1;
}

/* Card Header Badges */
.card-badges-bar {
  padding: 18px 24px 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}
.category-eyebrow {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: #94a3b8;
  font-weight: 700;
}
.pill-badges-group {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.badge-amg {
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 999px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.badge-noncdl {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}
.badge-cdl {
  background: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
  border: 1px solid rgba(59, 130, 246, 0.3);
}
.badge-engine {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

/* Vehicle Showcase Visual Section */
.vehicle-visual-wrap {
  position: relative;
  background: radial-gradient(circle at 50% 60%, #1e293b 0%, #0d131f 70%);
  padding: 24px 24px 16px;
  min-height: 320px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
.vehicle-main-img {
  max-width: 100%;
  max-height: 270px;
  height: auto;
  object-fit: contain;
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
  filter: drop-shadow(0 20px 25px rgba(0, 0, 0, 0.7));
}
.model-card:hover .vehicle-main-img {
  transform: scale(1.03);
}
.visual-angle-switcher {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 14px;
  z-index: 10;
  flex-wrap: wrap;
  justify-content: center;
}
.angle-btn {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #cbd5e1;
  font-size: 11px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 999px;
  cursor: pointer;
  transition: all 0.15s ease;
}
.angle-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  color: white;
}
.angle-btn.active {
  background: white;
  color: #0f172a;
  border-color: white;
  font-weight: 700;
}

/* Card Body */
.card-content {
  padding: 24px;
  display: flex;
  flex-direction: column;
  flex: 1;
}
.model-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 6px;
}
.model-title-row h2 {
  font-size: 26px;
  font-weight: 800;
  letter-spacing: -0.3px;
  margin: 0;
  color: white;
}
.model-brand-logo {
  height: 28px;
  object-fit: contain;
}
.model-descriptor {
  font-size: 13.5px;
  color: var(--amg-text-secondary);
  line-height: 1.5;
  margin: 0 0 20px;
}

/* Key Metrics 4-Pillar Grid (Mercedes AMG Spec Highlights) */
.specs-pillars-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 10px;
  padding: 12px;
  margin-bottom: 20px;
}
@media (max-width: 640px) {
  .specs-pillars-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
.spec-pillar-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 6px 4px;
  border-right: 1px solid rgba(255, 255, 255, 0.06);
}
.spec-pillar-box:last-child {
  border-right: none;
}
.pillar-val {
  font-size: 17px;
  font-weight: 800;
  color: white;
  letter-spacing: -0.2px;
}
.pillar-lbl {
  font-size: 10.5px;
  text-transform: uppercase;
  color: var(--amg-text-muted);
  letter-spacing: 0.5px;
  margin-top: 2px;
}
.pillar-sub {
  font-size: 9.5px;
  color: #94a3b8;
  margin-top: 2px;
}

/* Chassis Configs Tag Cloud */
.chassis-tags-section {
  margin-bottom: 20px;
}
.chassis-tags-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: var(--amg-text-muted);
  font-weight: 700;
  margin-bottom: 8px;
}
.chassis-tag-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.chassis-chip {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #cbd5e1;
  font-size: 11.5px;
  padding: 4px 10px;
  border-radius: 6px;
}

/* Pricing & Cooperative Section */
.pricing-coop-bar {
  background: rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 10px;
  padding: 14px 16px;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}
.price-display-wrap {
  display: flex;
  flex-direction: column;
}
.price-caption {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--amg-text-muted);
  font-weight: 600;
}
.price-amount {
  font-size: 22px;
  font-weight: 800;
  color: white;
  letter-spacing: -0.5px;
}
.price-note {
  font-size: 11px;
  color: #10b981;
  font-weight: 500;
}
.coop-logos-row {
  display: flex;
  align-items: center;
  gap: 10px;
}
.coop-badge-logo {
  height: 22px;
  background: white;
  padding: 2px 6px;
  border-radius: 4px;
  object-fit: contain;
}

/* Action CTA Buttons (Mercedes AMG Format) */
.card-cta-bar {
  margin-top: auto;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.btn-amg-primary {
  flex: 1.2;
  background: var(--odra-red);
  color: white;
  text-align: center;
  padding: 12px 18px;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: 1px solid var(--odra-red-bright);
}
.btn-amg-primary:hover {
  background: #8e1e22;
  box-shadow: 0 4px 16px var(--odra-red-glow);
  transform: translateY(-1px);
}
.btn-amg-secondary {
  flex: 1;
  background: rgba(255, 255, 255, 0.06);
  color: #f1f5f9;
  text-align: center;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  cursor: pointer;
}
.btn-amg-secondary:hover {
  background: rgba(255, 255, 255, 0.12);
  color: white;
  border-color: rgba(255, 255, 255, 0.3);
}

/* Comparison Section (Mercedes AMG Compare Models) */
.compare-section {
  background: var(--amg-card);
  border: 1px solid var(--amg-border);
  border-radius: 16px;
  padding: 36px 32px;
  margin-top: 48px;
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4);
}
.compare-header {
  text-align: center;
  margin-bottom: 32px;
}
.compare-header h2 {
  font-size: 28px;
  font-weight: 800;
  margin: 0 0 8px;
  color: white;
}
.compare-header p {
  font-size: 14.5px;
  color: var(--amg-text-secondary);
  max-width: 600px;
  margin: 0 auto;
}
.compare-table-wrap {
  overflow-x: auto;
}
.compare-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13.5px;
}
.compare-table th {
  padding: 14px 18px;
  text-align: left;
  border-bottom: 1px solid var(--amg-border);
  font-weight: 700;
  color: white;
}
.compare-table th.col-feature {
  width: 28%;
  color: #94a3b8;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.compare-table th.col-model {
  width: 36%;
  font-size: 16px;
  background: rgba(255, 255, 255, 0.02);
}
.compare-table td {
  padding: 14px 18px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  color: #cbd5e1;
  vertical-align: top;
}
.compare-table td.feature-title {
  font-weight: 600;
  color: #f1f5f9;
}
.compare-table tr:hover td {
  background: rgba(255, 255, 255, 0.02);
}
.compare-highlight {
  color: white;
  font-weight: 700;
}
.check-green {
  color: #10b981;
  font-weight: 700;
  margin-right: 4px;
}

/* Modal Specs Dialog */
.amg-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(8px);
  z-index: 1000;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
.amg-modal-backdrop.open {
  display: flex;
}
.amg-modal-card {
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 16px;
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
  animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalPop {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}
.amg-modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #1e293b;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #111827;
}
.amg-modal-header h3 {
  margin: 0;
  font-size: 20px;
  font-weight: 800;
  color: white;
  display: flex;
  align-items: center;
  gap: 10px;
}
.amg-modal-close {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: #94a3b8;
  font-size: 20px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.amg-modal-close:hover {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}
.amg-modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
  font-size: 14px;
  line-height: 1.6;
}
.modal-tabs-nav {
  display: flex;
  gap: 8px;
  border-bottom: 1px solid #334155;
  margin-bottom: 20px;
  padding-bottom: 12px;
  overflow-x: auto;
}
.modal-tab-btn {
  background: transparent;
  border: none;
  color: #94a3b8;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}
.modal-tab-btn.active {
  background: var(--odra-red);
  color: white;
}
.modal-tab-pane {
  display: none;
}
.modal-tab-pane.active {
  display: block;
}
.specs-table {
  width: 100%;
  border-collapse: collapse;
}
.specs-table td {
  padding: 10px 14px;
  border-bottom: 1px solid #1e293b;
  font-size: 13.5px;
}
.specs-table td:first-child {
  color: #94a3b8;
  width: 40%;
  font-weight: 500;
}
.specs-table td:last-child {
  color: #f1f5f9;
  font-weight: 600;
}
.amg-modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #1e293b;
  background: #111827;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}
</style>
@endpush

@section('content')
<div class="catalog-container">

  <!-- Mercedes-AMG Style Dark Luxury Hero Banner -->
  <div class="amg-hero">
    <div class="hero-tagline">
      <span>&#9733;</span> ODRA Performance Showcase &middot; 2027 Model Year Lineup
    </div>
    <h1>DISCOVER ODRA MECHANICAL SWEEPERS</h1>
    <p class="hero-sub">
      Precision engineering, maximum debris pickup, and rugged durability for North American municipalities, state DOTs, and highway contractors. Available on commercial non-CDL and CDL chassis with 10-foot variable discharge dumping.
    </p>

    <div class="hero-highlights-strip">
      <div class="hero-stat">
        <span class="hero-stat-icon">&#10003;</span>
        <span><strong>100% Mechanical Broom</strong> High-Production Efficiency</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-icon">&#10003;</span>
        <span><strong>10-Ft Variable Dump</strong> Discharge to Any Hauler</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-icon">&#10003;</span>
        <span><strong>Non-CDL Commercial</strong> Class 5 &amp; Class 6 Options</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-icon">&#10003;</span>
        <span><strong>Pre-Approved</strong> Sourcewell, HGAC &amp; BuyBoard</span>
      </div>
    </div>
  </div>

  <!-- Mercedes-Benz AMG Style Filter Bar -->
  <div class="filter-bar-wrap">
    <div class="filter-pills">
      <button class="filter-pill active" data-filter="all">All Models (2)</button>
      <button class="filter-pill" data-filter="mt4h">MT4H Series (Dual-Engine)</button>
      <button class="filter-pill" data-filter="ms5h">MS5H Series (Single-Engine)</button>
      <button class="filter-pill" data-filter="non-cdl">Non-CDL Commercial</button>
      <button class="filter-pill" data-filter="cooperative">Cooperative Approved</button>
      <button class="filter-pill" id="jumpToCompareBtn" style="background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.25); color: #fff;">
        &harr; Compare Side-by-Side
      </button>
    </div>

    <div class="filter-search-box">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      <input type="text" id="modelSearchInput" placeholder="Search chassis or specs...">
    </div>
  </div>

  <!-- Models Grid Showcase -->
  <div class="models-grid" id="modelsGrid">

    <!-- Card 1: ODRA MT4H -->
    <div class="model-card" data-model="MT4H" data-category="mt4h non-cdl cooperative cabover">
      <div class="card-badges-bar">
        <span class="category-eyebrow">MECHANICAL BROOM SWEEPER &middot; 2027MY</span>
        <div class="pill-badges-group">
          <span class="badge-amg badge-noncdl">CLASS 5/6 &middot; NON-CDL</span>
          <span class="badge-amg badge-engine">DUAL ENGINE &middot; TIER 4-F 65HP</span>
        </div>
      </div>

      <!-- Vehicle Visual Showcase with Angle Switcher -->
      <div class="vehicle-visual-wrap">
        <img id="mt4hMainImg" class="vehicle-main-img" src="{{ asset('images/models/landing-mt4h-diesel.webp') }}" alt="ODRA MT4H Mechanical Broom Sweeper">
        
        <div class="visual-angle-switcher">
          <button class="angle-btn active" onclick="switchImage('mt4h', '{{ asset('images/models/landing-mt4h-diesel.webp') }}', this)">Exterior Profile</button>
          <button class="angle-btn" onclick="switchImage('mt4h', '{{ asset('images/models/mt4h-banner-half.webp') }}', this)">Hopper &amp; Elevator</button>
          <button class="angle-btn" onclick="switchImage('mt4h', '{{ asset('images/models/landing-mt4h-gas.webp') }}', this)">Gasoline Option</button>
          <button class="angle-btn" onclick="switchImage('mt4h', '{{ asset('images/models/product-1720523455.webp') }}', this)">HD Close-up</button>
        </div>
      </div>

      <div class="card-content">
        <div class="model-title-row">
          <h2>ODRA MT4H</h2>
          <img src="{{ asset('images/models/mt4h-ban-logo.svg') }}" alt="MT4H Logo" class="model-brand-logo">
        </div>
        <p class="model-descriptor">
          The ultimate in agile urban sweeping. Dual-engine Tier 4-Final auxiliary power paired with a 109" wheelbase cabover chassis delivers unmatched turning radius, high-density hopper packing, and left or right variable dumping up to 10 feet.
        </p>

        <!-- Mercedes AMG 4-Pillar Spec Grid -->
        <div class="specs-pillars-grid">
          <div class="spec-pillar-box">
            <span class="pillar-val">4.0 yd&sup3;</span>
            <span class="pillar-lbl">Hopper Volume</span>
            <span class="pillar-sub">10-ft Var. Dump</span>
          </div>
          <div class="spec-pillar-box">
            <span class="pillar-val">220 Gal</span>
            <span class="pillar-lbl">Water System</span>
            <span class="pillar-sub">4 Spray Bars</span>
          </div>
          <div class="spec-pillar-box">
            <span class="pillar-val">109" WB</span>
            <span class="pillar-lbl">Wheelbase</span>
            <span class="pillar-sub">Tight Urban Radius</span>
          </div>
          <div class="spec-pillar-box">
            <span class="pillar-val">65 HP</span>
            <span class="pillar-lbl">Aux Engine</span>
            <span class="pillar-sub">Kubota Tier 4-F</span>
          </div>
        </div>

        <!-- Available Chassis Configurations -->
        <div class="chassis-tags-section">
          <div class="chassis-tags-label">Standard Chassis Configurations</div>
          <div class="chassis-tag-list">
            <span class="chassis-chip">&#10003; Isuzu NRR Cabover 19,500 GVWR &middot; Left-Side Dump</span>
            <span class="chassis-chip">&#10003; Isuzu NRR Cabover 19,500 GVWR &middot; Right-Side Dump</span>
            <span class="chassis-chip">&#10003; Ford F-550XL Conventional 19,000 GVWR &middot; Left-Side Dump</span>
          </div>
        </div>

        <!-- Pricing & Cooperative Contracts -->
        <div class="pricing-coop-bar">
          <div class="price-display-wrap">
            @if ($isManager)
              <span class="price-caption">Starting Commercial Pricing</span>
              <span class="price-amount">$257,493 <span style="font-size:13px;font-weight:normal;color:#94a3b8;">MSRP</span></span>
              <span class="price-note">&bull; Sourcewell List: $307,378 &bull; Net: $270,493</span>
            @elseif ($isAdmin)
              <span class="price-caption">Master Model Code</span>
              <span class="price-amount" style="font-size: 18px; color: #cbd5e1;">MT4H-SERIES</span>
              <span style="font-size: 11px; color: #94a3b8;">Technical Admin Master &middot; Financials Restricted</span>
            @else
              <span class="price-caption">Starting Baseline Price</span>
              <span class="price-amount">$257,493 <span style="font-size:13px;font-weight:normal;color:#94a3b8;">MSRP</span></span>
              <span class="price-note">&#10003; Pre-approved Cooperative Pricing Available</span>
            @endif
          </div>

          <div class="coop-logos-row">
            <img src="{{ asset('images/logo-sourcewell.png') }}" alt="Sourcewell" class="coop-badge-logo" title="Sourcewell Contract #093021-ODR">
            <img src="{{ asset('images/logo-hgac.png') }}" alt="H-GAC" class="coop-badge-logo" title="H-GAC Pre-Approved">
            <img src="{{ asset('images/logo-buyboard.png') }}" alt="BuyBoard" class="coop-badge-logo" title="BuyBoard National Cooperative">
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="card-cta-bar">
          <a href="{{ route('sales.quotes.create') }}?model=MT4H" class="btn-amg-primary">
            <span>Configure in Quote Builder</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </a>
          <button type="button" class="btn-amg-secondary" onclick="openSpecModal('MT4H')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <span>Technical Specs</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Card 2: ODRA MS5H -->
    <div class="model-card" data-model="MS5H" data-category="ms5h non-cdl cdl cooperative conventional">
      <div class="card-badges-bar">
        <span class="category-eyebrow">MECHANICAL BROOM SWEEPER &middot; 2027MY</span>
        <div class="pill-badges-group">
          <span class="badge-amg badge-cdl">CLASS 6/7 &middot; DUAL RATED</span>
          <span class="badge-amg badge-engine">SINGLE ENGINE &middot; CUMMINS 300HP</span>
        </div>
      </div>

      <!-- Vehicle Visual Showcase with Angle Switcher -->
      <div class="vehicle-visual-wrap">
        <img id="ms5hMainImg" class="vehicle-main-img" src="{{ asset('images/models/landing-ms5h.webp') }}" alt="ODRA MS5H Mechanical Street Sweeper">
        
        <div class="visual-angle-switcher">
          <button class="angle-btn active" onclick="switchImage('ms5h', '{{ asset('images/models/landing-ms5h.webp') }}', this)">Exterior Profile</button>
          <button class="angle-btn" onclick="switchImage('ms5h', '{{ asset('images/models/ms5h-cleanup.webp') }}', this)">Road Cleanup</button>
          <button class="angle-btn" onclick="switchImage('ms5h', '{{ asset('images/models/ms5h-built.webp') }}', this)">Chassis &amp; Build</button>
          <button class="angle-btn" onclick="switchImage('ms5h', '{{ asset('images/models/ms5h-banner-half.webp') }}', this)">3/4 Front View</button>
        </div>
      </div>

      <div class="card-content">
        <div class="model-title-row">
          <h2>ODRA MS5H</h2>
          <img src="{{ asset('images/models/ms5h-ban-logo.svg') }}" alt="MS5H Logo" class="model-brand-logo">
        </div>
        <p class="model-descriptor">
          Heavy-duty road sweeping powerhouse. Built around a single 300HP Cummins B6.7 engine on a Freightliner M2 Plus chassis, eliminating secondary engine maintenance while delivering an expansive 5-cubic-yard hopper and 355-gallon dust-suppression system.
        </p>

        <!-- Mercedes AMG 4-Pillar Spec Grid -->
        <div class="specs-pillars-grid">
          <div class="spec-pillar-box">
            <span class="pillar-val">5.0 yd&sup3;</span>
            <span class="pillar-lbl">Hopper Volume</span>
            <span class="pillar-sub">10-ft Var. Dump</span>
          </div>
          <div class="spec-pillar-box">
            <span class="pillar-val">355 Gal</span>
            <span class="pillar-lbl">Water System</span>
            <span class="pillar-sub">4 Spray Bars</span>
          </div>
          <div class="spec-pillar-box">
            <span class="pillar-val">148" WB</span>
            <span class="pillar-lbl">Wheelbase</span>
            <span class="pillar-sub">Highway Stability</span>
          </div>
          <div class="spec-pillar-box">
            <span class="pillar-val">300 HP</span>
            <span class="pillar-lbl">Cummins B6.7</span>
            <span class="pillar-sub">Single-Engine Tech</span>
          </div>
        </div>

        <!-- Available Chassis Configurations -->
        <div class="chassis-tags-section">
          <div class="chassis-tags-label">Standard Chassis Configurations</div>
          <div class="chassis-tag-list">
            <span class="chassis-chip">&#10003; Conventional Class 6 &middot; 25,500 GVWR &middot; Non-CDL Operation</span>
            <span class="chassis-chip">&#10003; Conventional Class 7 &middot; 26,000+ GVWR &middot; CDL-Required Heavy Duty</span>
          </div>
        </div>

        <!-- Pricing & Cooperative Contracts -->
        <div class="pricing-coop-bar">
          <div class="price-display-wrap">
            @if ($isManager)
              <span class="price-caption">Starting Commercial Pricing</span>
              <span class="price-amount">$349,149 <span style="font-size:13px;font-weight:normal;color:#94a3b8;">MSRP</span></span>
              <span class="price-note">&bull; Sourcewell List: $411,533 &bull; Net: $362,149</span>
            @elseif ($isAdmin)
              <span class="price-caption">Master Model Code</span>
              <span class="price-amount" style="font-size: 18px; color: #cbd5e1;">MS5H-SERIES</span>
              <span style="font-size: 11px; color: #94a3b8;">Technical Admin Master &middot; Financials Restricted</span>
            @else
              <span class="price-caption">Starting Baseline Price</span>
              <span class="price-amount">$349,149 <span style="font-size:13px;font-weight:normal;color:#94a3b8;">MSRP</span></span>
              <span class="price-note">&#10003; Pre-approved Cooperative Pricing Available</span>
            @endif
          </div>

          <div class="coop-logos-row">
            <img src="{{ asset('images/logo-sourcewell.png') }}" alt="Sourcewell" class="coop-badge-logo" title="Sourcewell Contract #093021-ODR">
            <img src="{{ asset('images/logo-hgac.png') }}" alt="H-GAC" class="coop-badge-logo" title="H-GAC Pre-Approved">
            <img src="{{ asset('images/logo-buyboard.png') }}" alt="BuyBoard" class="coop-badge-logo" title="BuyBoard National Cooperative">
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="card-cta-bar">
          <a href="{{ route('sales.quotes.create') }}?model=MS5H" class="btn-amg-primary">
            <span>Configure in Quote Builder</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </a>
          <button type="button" class="btn-amg-secondary" onclick="openSpecModal('MS5H')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <span>Technical Specs</span>
          </button>
        </div>
      </div>
    </div>

  </div>

  <!-- Side-by-Side Model Comparison Matrix (Mercedes "Compare Models" Style) -->
  <div class="compare-section" id="compareSection">
    <div class="compare-header">
      <h2>SIDE-BY-SIDE ENGINEERING COMPARISON</h2>
      <p>Compare key dimensions, capacities, and powertrain options between the compact ODRA MT4H and heavy-duty ODRA MS5H.</p>
    </div>

    <div class="compare-table-wrap">
      <table class="compare-table">
        <thead>
          <tr>
            <th class="col-feature">Specification / Feature</th>
            <th class="col-model">
              <div style="display:flex;align-items:center;gap:8px;">
                <span class="compare-highlight">ODRA MT4H</span>
                <span class="badge-amg badge-noncdl" style="font-size:10px;">Cabover</span>
              </div>
            </th>
            <th class="col-model">
              <div style="display:flex;align-items:center;gap:8px;">
                <span class="compare-highlight">ODRA MS5H</span>
                <span class="badge-amg badge-cdl" style="font-size:10px;">Conventional</span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="feature-title">Primary Operational Mission</td>
            <td>Municipal streets, urban alleys, tight cul-de-sacs, milling cleanups</td>
            <td>State highways, major arterial roads, heavy construction, industrial paving</td>
          </tr>
          <tr>
            <td class="feature-title">Hopper Capacity</td>
            <td><strong class="compare-highlight">4.0 Cubic Yards</strong> (Volumetric)</td>
            <td><strong class="compare-highlight">5.0 Cubic Yards</strong> (Heavy-Duty Volumetric)</td>
          </tr>
          <tr>
            <td class="feature-title">Hopper Dumping Mechanism</td>
            <td>Variable height discharge up to <strong class="compare-highlight">10 Feet</strong> (Left or Right dump)</td>
            <td>Variable height discharge up to <strong class="compare-highlight">10 Feet</strong> (Left-side dump standard)</td>
          </tr>
          <tr>
            <td class="feature-title">Engine Architecture</td>
            <td>
              <span class="check-green">&#10003;</span> <strong>Dual Engine:</strong> Chassis propulsion + Tier 4-Final 65HP auxiliary
            </td>
            <td>
              <span class="check-green">&#10003;</span> <strong>Single Engine:</strong> 300HP Cummins B6.7 powers both chassis &amp; hydraulics
            </td>
          </tr>
          <tr>
            <td class="feature-title">Water Spray &amp; Dust Suppression</td>
            <td><strong>220 Gallons</strong> poly tank &middot; 4 spray bars with high-pressure pump</td>
            <td><strong>355 Gallons</strong> high-capacity tank &middot; 4 spray bars with multi-zone control</td>
          </tr>
          <tr>
            <td class="feature-title">Standard Chassis Platform</td>
            <td>Brand new 2027MY Isuzu NRR Cabover (19,500 GVWR) or Ford F-550XL</td>
            <td>Brand new 2027MY Freightliner M2 Plus (26,000 GVWR)</td>
          </tr>
          <tr>
            <td class="feature-title">Commercial Driver's License (CDL)</td>
            <td><span class="check-green">&#10003;</span> <strong>Non-CDL Required</strong> across all MT4H configurations</td>
            <td><span class="check-green">&#10003;</span> <strong>Non-CDL (Class 6)</strong> standard; optional Class 7 CDL rated</td>
          </tr>
          <tr>
            <td class="feature-title">Wheelbase &amp; Maneuverability</td>
            <td><strong>109" Wheelbase</strong> &middot; Extreme tight turning radius for tight streetscapes</td>
            <td><strong>148" Wheelbase</strong> &middot; Extended high-speed highway sweeping stability</td>
          </tr>
          <tr>
            <td class="feature-title">Sweeping Brooms</td>
            <td>36" Hydraulic Gutter Brooms &middot; 60" Strip Main Broom ILO Standard</td>
            <td>36" Heavy-Duty Gutter Brooms &middot; 60" High-Density Strip Main Broom</td>
          </tr>
          <tr>
            <td class="feature-title">Debris Elevator System</td>
            <td>Continuous forward-feed multi-flight conveyor belt with reverse jam clear</td>
            <td>Heavy-duty continuous conveyor with optional hydraulic elevator flush</td>
          </tr>
          <tr>
            <td class="feature-title">Lighting &amp; Visibility</td>
            <td>Full LED chassis lighting, work lamps, cab beacon &amp; rear safety strobe options</td>
            <td>Full LED chassis lighting, dual gutter broom work lights, LED arrow stick ready</td>
          </tr>
          <tr>
            <td class="feature-title">Dualized Steering Conversion</td>
            <td>Available factory dual steering conversion for curb-side sweeping precision</td>
            <td>Available dual steering conversion for dual-side highway gutter access</td>
          </tr>
          <tr>
            <td class="feature-title">Stainless Steel Hopper Option</td>
            <td><span class="check-green">&#10003;</span> Available 304 Stainless Steel Hopper body ILO Standard</td>
            <td>Heavy-gauge welded abrasion-resistant steel hopper shell</td>
          </tr>
          <tr>
            <td class="feature-title">Cooperative Purchasing Pre-Approvals</td>
            <td>Sourcewell (#093021-ODR), H-GAC, BuyBoard, Minnesota State Contract</td>
            <td>Sourcewell (#093021-ODR), H-GAC, BuyBoard, Minnesota State Contract</td>
          </tr>
          <tr>
            <td class="feature-title">Actions</td>
            <td>
              <a href="{{ route('sales.quotes.create') }}?model=MT4H" class="btn-amg-primary" style="padding: 8px 14px; font-size: 12px;">Configure MT4H &rarr;</a>
            </td>
            <td>
              <a href="{{ route('sales.quotes.create') }}?model=MS5H" class="btn-amg-primary" style="padding: 8px 14px; font-size: 12px;">Configure MS5H &rarr;</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Interactive Technical Specifications Modal Dialog -->
<div class="amg-modal-backdrop" id="specModal" onclick="if(event.target===this) closeSpecModal()">
  <div class="amg-modal-card">
    <div class="amg-modal-header">
      <h3 id="modalTitle">Technical Specifications</h3>
      <button class="amg-modal-close" onclick="closeSpecModal()">&times;</button>
    </div>

    <div class="amg-modal-body">
      <div class="modal-tabs-nav">
        <button class="modal-tab-btn active" onclick="switchModalTab(event, 'tab-dimensions')">Dimensions &amp; Chassis</button>
        <button class="modal-tab-btn" onclick="switchModalTab(event, 'tab-powertrain')">Engine &amp; Hydraulics</button>
        <button class="modal-tab-btn" onclick="switchModalTab(event, 'tab-sweeping')">Brooms &amp; Elevator</button>
        <button class="modal-tab-btn" onclick="switchModalTab(event, 'tab-water')">Dust &amp; Spray System</button>
        <button class="modal-tab-btn" onclick="switchModalTab(event, 'tab-enhancements')">Enhancements Catalog</button>
      </div>

      <div id="tab-dimensions" class="modal-tab-pane active">
        <table class="specs-table" id="specsDimensionsTable">
          <!-- Populated dynamically via JS -->
        </table>
      </div>

      <div id="tab-powertrain" class="modal-tab-pane">
        <table class="specs-table" id="specsPowertrainTable">
          <!-- Populated dynamically via JS -->
        </table>
      </div>

      <div id="tab-sweeping" class="modal-tab-pane">
        <table class="specs-table" id="specsSweepingTable">
          <!-- Populated dynamically via JS -->
        </table>
      </div>

      <div id="tab-water" class="modal-tab-pane">
        <table class="specs-table" id="specsWaterTable">
          <!-- Populated dynamically via JS -->
        </table>
      </div>

      <div id="tab-enhancements" class="modal-tab-pane">
        <div style="margin-bottom:12px;font-size:12.5px;color:#94a3b8;">
          All enhancement options can be added directly into quotations via the interactive Quote Builder:
        </div>
        <table class="specs-table" id="specsEnhancementsTable">
          <!-- Populated dynamically via JS -->
        </table>
      </div>
    </div>

    <div class="amg-modal-footer">
      <button type="button" class="btn-amg-secondary" onclick="closeSpecModal()">Close</button>
      <a id="modalConfigureBtn" href="{{ route('sales.quotes.create') }}" class="btn-amg-primary">
        <span>Launch Quote Builder for this Model &rarr;</span>
      </a>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
// Technical Specifications Data Repository
const SPEC_DATA = {
  MT4H: {
    title: 'ODRA MT4H &middot; Engineering Specifications',
    configureUrl: "{{ route('sales.quotes.create') }}?model=MT4H",
    dimensions: [
      { label: 'Hopper Volume', val: '4.0 Cubic Yards Volumetric Capacity' },
      { label: 'Dump Height', val: 'Variable discharge up to 10 Feet' },
      { label: 'Dumping Direction', val: 'Left-Side Dump Standard (Right-Side Dump Option available)' },
      { label: 'Chassis Type', val: '2027MY Isuzu NRR Cabover / Ford F-550XL' },
      { label: 'Gross Vehicle Weight (GVWR)', val: '19,500 lbs (Isuzu) / 19,000 lbs (Ford) &mdash; Class 5/6' },
      { label: 'Commercial Driver License', val: 'Non-CDL Required (Standard Class D driver license)' },
      { label: 'Wheelbase', val: '109 inches &mdash; Ultra-tight urban maneuverability' },
      { label: 'Brakes', val: '4-wheel hydraulic disc brakes with ABS' },
      { label: 'Chassis Lighting', val: 'Full LED exterior & interior lighting package' }
    ],
    powertrain: [
      { label: 'Primary Propulsion', val: 'Chassis turbo-diesel (Isuzu 5.2L 4HK1-TC 215HP)' },
      { label: 'Auxiliary Sweeper Engine', val: 'Kubota Tier 4-Final 65HP Diesel Engine' },
      { label: 'Auxiliary Fuel Source', val: 'Shared diesel reservoir with electric transfer pump' },
      { label: 'Hydraulic System', val: 'Tandem gear pump with proportional control valves' },
      { label: 'Hydraulic Fluid Reservoir', val: '35 Gallons with 10-micron filtration & oil cooler' },
      { label: 'Electrical System', val: '12V dual heavy-duty batteries with master cutoff switch' }
    ],
    sweeping: [
      { label: 'Main Broom Width', val: '60 inches &times; 36 inches diameter' },
      { label: 'Main Broom Type', val: 'Prefabricated polypropylene disposable or strip core' },
      { label: 'Gutter Brooms', val: 'Dual 36-inch diameter steel wire segment brooms' },
      { label: 'Gutter Broom Tilt', val: 'In-cab electric remote tilt adjustment' },
      { label: 'Sweeping Path', val: '120 inches with both gutter brooms extended' },
      { label: 'Conveyor / Elevator', val: 'Continuous 7-flight squeegee belt conveyor with reverse clear' },
      { label: 'Drag Shoes', val: 'Tungsten carbide shoes with optional pivot arms' }
    ],
    water: [
      { label: 'Water Tank Capacity', val: '220 Gallons polyethylene rust-proof reservoir' },
      { label: 'Spray Bars', val: '4 stainless steel spray bars (front bumper, brooms, hopper)' },
      { label: 'Water Pump', val: 'Electric diaphragm pump delivering 3.6 GPM at 45 PSI' },
      { label: 'Filtration', val: 'Cleanable 50-mesh in-line water filter' },
      { label: 'Elevator Flush System', val: 'Optional high-volume elevator washout manifold' }
    ],
    enhancements: [
      { label: 'Front LED Strobe Light', val: 'Mounted behind cab, visible from front ($529 MSRP)' },
      { label: 'Rear Mounted LED Light Bar', val: 'High-intensity multi-pattern traffic director ($2,293 MSRP)' },
      { label: 'Strip Main Broom ILO Standard', val: 'Heavy-duty chevron strip broom ($746 MSRP)' },
      { label: 'Elevator System Flush', val: 'Quick-connect water purge system ($1,405 MSRP)' },
      { label: 'Stainless Steel Hopper', val: '304 stainless steel hopper body replacement ($13,000 MSRP)' },
      { label: 'Dualization for MT4H Chassis', val: 'Dual steering column, pedals & gauge cluster ($20,400 MSRP)' },
      { label: 'Extended Warranty', val: '2nd Year ($6,106) / 3rd Year ($11,554) Module Parts & Labor' }
    ]
  },
  MS5H: {
    title: 'ODRA MS5H &middot; Engineering Specifications',
    configureUrl: "{{ route('sales.quotes.create') }}?model=MS5H",
    dimensions: [
      { label: 'Hopper Volume', val: '5.0 Cubic Yards Heavy-Duty Volumetric Capacity' },
      { label: 'Dump Height', val: 'Variable discharge up to 10 Feet' },
      { label: 'Dumping Direction', val: 'Left-Side Dump Standard with heavy-duty twin lift cylinders' },
      { label: 'Chassis Type', val: '2027MY Freightliner M2 Plus Conventional commercial cab' },
      { label: 'Gross Vehicle Weight (GVWR)', val: '25,500 lbs (Class 6 Non-CDL) or 26,000+ lbs (Class 7 CDL)' },
      { label: 'Commercial Driver License', val: 'Available in both Non-CDL (Class 6) and CDL-Required platforms' },
      { label: 'Wheelbase', val: '148 inches &mdash; High-speed road tracking and stability' },
      { label: 'Brakes', val: 'Full pneumatic air brakes with Meritor automatic slack adjusters' },
      { label: 'Chassis Lighting', val: 'Full LED exterior lamps & heavy-duty rear bumper assembly' }
    ],
    powertrain: [
      { label: 'Single-Engine Powertrain', val: 'Cummins B6.7 300HP Diesel Engine (No aux engine needed)' },
      { label: 'Transmission', val: 'Allison 2500 RDS automatic with PTO transmission output' },
      { label: 'Emissions Compliance', val: 'EPA Tier 4 / CARB certified with DEF selective reduction' },
      { label: 'Hydraulic System', val: 'Load-sensing variable displacement piston pump' },
      { label: 'Hydraulic Fluid Reservoir', val: '45 Gallons with thermostatically controlled oil cooler' },
      { label: 'Fuel Capacity', val: '50 Gallons aluminum single fuel tank' }
    ],
    sweeping: [
      { label: 'Main Broom Width', val: '60 inches &times; 36 inches diameter heavy-duty broom' },
      { label: 'Main Broom Type', val: 'Full floating suspension with hydraulic down-pressure' },
      { label: 'Gutter Brooms', val: 'Dual 36-inch diameter steel wire brooms with shock protection' },
      { label: 'Gutter Broom Tilt', val: 'In-cab dual-axis joystick control with angle tilt' },
      { label: 'Sweeping Path', val: 'Up to 132 inches with dual gutter brooms extended' },
      { label: 'Conveyor / Elevator', val: 'Heavy-duty 11-flight conveyor with abrasion-resistant belt' },
      { label: 'Drag Shoes', val: 'Tungsten carbide shoes with optional pivot arms' }
    ],
    water: [
      { label: 'Water Tank Capacity', val: '355 Gallons heavy-gauge UV-stabilized reservoir' },
      { label: 'Spray Bars', val: '4 high-output stainless steel spray bars with atomizing nozzles' },
      { label: 'Water Pump', val: 'Heavy-duty twin electric diaphragm pumps (5.0 GPM)' },
      { label: 'Filtration', val: 'Dual cleanable suction strainers with shutoff ball valves' },
      { label: 'Hydrant Fill System', val: '2.5-inch quick-connect hydrant fill hose with air gap' }
    ],
    enhancements: [
      { label: 'Front LED Strobe Light', val: 'Mounted behind cab, visible from front ($529 MSRP)' },
      { label: 'Rear Mounted LED Light Bar', val: 'High-intensity multi-pattern traffic director ($2,293 MSRP)' },
      { label: 'Strip Main Broom ILO Standard', val: 'Chevron pattern high-density sweeping core ($746 MSRP)' },
      { label: 'Elevator System Flush', val: 'Quick-connect water purge manifold ($1,405 MSRP)' },
      { label: 'Dualized Conversion on Chassis', val: 'Dual steering column & pedals on Freightliner ($19,519 MSRP)' },
      { label: 'Drag Shoes Pivot Arms', val: 'Pivot drag shoe arms ILO standard ($998 MSRP)' },
      { label: 'Extended Warranty', val: '2nd Year ($6,106) / 3rd Year ($11,554) Module Parts & Labor' }
    ]
  }
};

// Switch Hero vehicle image with active button state
function switchImage(modelCode, imageSrc, btnElement) {
  const imgId = modelCode.toLowerCase() + 'MainImg';
  const imgEl = document.getElementById(imgId);
  if (imgEl) {
    imgEl.style.opacity = '0';
    setTimeout(() => {
      imgEl.src = imageSrc;
      imgEl.style.opacity = '1';
    }, 150);
  }
  const parent = btnElement.parentElement;
  parent.querySelectorAll('.angle-btn').forEach(b => b.classList.remove('active'));
  btnElement.classList.add('active');
}

// Category filter tabs logic
document.querySelectorAll('.filter-pill[data-filter]').forEach(pill => {
  pill.addEventListener('click', function() {
    document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
    this.classList.add('active');
    
    const filter = this.dataset.filter;
    const cards = document.querySelectorAll('.model-card');

    cards.forEach(card => {
      if (filter === 'all') {
        card.style.display = 'flex';
      } else {
        const cat = card.dataset.category || '';
        if (cat.includes(filter)) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      }
    });
  });
});

// Real-time search filter
const searchInput = document.getElementById('modelSearchInput');
if (searchInput) {
  searchInput.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.model-card').forEach(card => {
      const text = card.textContent.toLowerCase();
      if (!q || text.includes(q)) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  });
}

// Jump to comparison
const jumpBtn = document.getElementById('jumpToCompareBtn');
if (jumpBtn) {
  jumpBtn.addEventListener('click', () => {
    document.getElementById('compareSection').scrollIntoView({ behavior: 'smooth' });
  });
}

// Open Tech Specs Modal
function openSpecModal(modelCode) {
  const data = SPEC_DATA[modelCode];
  if (!data) return;

  document.getElementById('modalTitle').innerHTML = data.title;
  document.getElementById('modalConfigureBtn').href = data.configureUrl;

  renderSpecsTable('specsDimensionsTable', data.dimensions);
  renderSpecsTable('specsPowertrainTable', data.powertrain);
  renderSpecsTable('specsSweepingTable', data.sweeping);
  renderSpecsTable('specsWaterTable', data.water);
  renderSpecsTable('specsEnhancementsTable', data.enhancements);

  document.getElementById('specModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeSpecModal() {
  document.getElementById('specModal').classList.remove('open');
  document.body.style.overflow = '';
}

function renderSpecsTable(tableId, rows) {
  const tbody = document.getElementById(tableId);
  if (!tbody) return;
  tbody.innerHTML = rows.map(r => `
    <tr>
      <td>${r.label}</td>
      <td>${r.val}</td>
    </tr>
  `).join('');
}

function switchModalTab(evt, tabId) {
  document.querySelectorAll('.modal-tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.modal-tab-pane').forEach(p => p.classList.remove('active'));
  evt.target.classList.add('active');
  document.getElementById(tabId).classList.add('active');
}

// Close modal on Escape
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeSpecModal();
});
</script>
@endpush
