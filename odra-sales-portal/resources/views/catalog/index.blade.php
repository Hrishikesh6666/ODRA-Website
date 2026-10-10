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

/* Chassis Configs Selector */
.chassis-tags-section {
  margin-bottom: 18px;
}
.chassis-tags-label {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: var(--amg-text-muted);
  font-weight: 700;
  margin-bottom: 8px;
}
.chassis-selector-list {
  display: flex;
  flex-direction: column;
  gap: 7px;
}
.chassis-chip-btn {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  width: 100%;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 8px 12px;
  color: #cbd5e1;
  font-size: 12px;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  user-select: none;
}
.chassis-chip-btn:hover {
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(255, 255, 255, 0.25);
  color: white;
}
.chassis-chip-btn.active {
  background: linear-gradient(90deg, rgba(172, 37, 41, 0.22) 0%, rgba(26, 32, 46, 0.6) 100%);
  border-color: var(--odra-red-bright);
  color: white;
  box-shadow: 0 0 12px rgba(172, 37, 41, 0.25);
}
.chassis-btn-left {
  display: flex;
  align-items: center;
  gap: 8px;
  overflow: hidden;
}
.chassis-radio-indicator {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.2s ease;
}
.chassis-chip-btn.active .chassis-radio-indicator {
  border-color: var(--odra-red-bright);
  background: var(--odra-red-bright);
  box-shadow: 0 0 8px rgba(220, 38, 38, 0.6);
}
.chassis-chip-btn.active .chassis-radio-indicator::after {
  content: '';
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: white;
}
.chassis-label-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 11.8px;
}
.chassis-badge-included {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 2px 7px;
  border-radius: 999px;
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
  flex-shrink: 0;
}

/* Procurement / Pricing Basis Selector */
.basis-selector-section {
  margin-bottom: 16px;
}
.basis-selector-label {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: var(--amg-text-muted);
  font-weight: 700;
  margin-bottom: 8px;
}
.basis-pills-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.basis-pill-btn {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #94a3b8;
  font-size: 11.5px;
  font-weight: 600;
  padding: 5px 11px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.18s ease;
  user-select: none;
}
.basis-pill-btn:hover {
  background: rgba(255, 255, 255, 0.09);
  color: white;
}
.basis-pill-btn.active {
  background: white;
  color: #0f172a;
  border-color: white;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(255, 255, 255, 0.25);
}

/* Upgrades & Factory Enhancements Configurator */
.upgrades-configurator-section {
  background: rgba(0, 0, 0, 0.22);
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 10px;
  padding: 12px 14px;
  margin-bottom: 20px;
  transition: border-color 0.2s ease;
}
.upgrades-configurator-section:hover {
  border-color: rgba(255, 255, 255, 0.14);
}
.upgrades-header-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  cursor: pointer;
  user-select: none;
}
.upgrades-title-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: #e2e8f0;
  font-weight: 700;
}
.upgrades-count-pill {
  font-size: 10.5px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
  transition: all 0.2s ease;
}
.upgrades-toggle-chevron {
  color: #94a3b8;
  font-size: 11px;
  transition: transform 0.2s ease;
}
.upgrades-header-bar.collapsed .upgrades-toggle-chevron {
  transform: rotate(-90deg);
}
.upgrades-body {
  margin-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.upgrade-option-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 7px;
  padding: 7px 10px;
  cursor: pointer;
  transition: all 0.18s ease;
  user-select: none;
}
.upgrade-option-card:hover {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.2);
}
.upgrade-option-card.selected {
  background: rgba(16, 185, 129, 0.09);
  border-color: rgba(16, 185, 129, 0.45);
  box-shadow: 0 0 10px rgba(16, 185, 129, 0.15);
}
.upgrade-card-left {
  display: flex;
  align-items: center;
  gap: 8px;
  overflow: hidden;
  flex: 1;
}
.upgrade-checkbox {
  width: 15px;
  height: 15px;
  border-radius: 4px;
  border: 1.5px solid rgba(255, 255, 255, 0.3);
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.15s ease;
}
.upgrade-option-card.selected .upgrade-checkbox {
  background: #10b981;
  border-color: #10b981;
}
.upgrade-checkbox::after {
  content: '';
  display: none;
  width: 4px;
  height: 7px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
  margin-bottom: 2px;
}
.upgrade-option-card.selected .upgrade-checkbox::after {
  display: block;
}
.upgrade-cat-tag {
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 2px 5px;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.08);
  color: #94a3b8;
  flex-shrink: 0;
}
.upgrade-name {
  font-size: 11.5px;
  color: #e2e8f0;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.upgrade-price-tag {
  font-size: 11.5px;
  font-weight: 700;
  color: #34d399;
  flex-shrink: 0;
  letter-spacing: -0.2px;
}
.show-more-options-btn {
  background: transparent;
  border: 1px dashed rgba(255, 255, 255, 0.15);
  color: #94a3b8;
  padding: 6px;
  border-radius: 6px;
  font-size: 11.5px;
  cursor: pointer;
  text-align: center;
  transition: all 0.15s ease;
  margin-top: 2px;
}
.show-more-options-btn:hover {
  color: white;
  border-color: rgba(255, 255, 255, 0.35);
  background: rgba(255, 255, 255, 0.04);
}
.more-upgrades-drawer {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

/* Pricing & Cooperative Section */
.pricing-coop-bar {
  background: rgba(0, 0, 0, 0.28);
  border: 1px solid rgba(255, 255, 255, 0.08);
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
  font-size: 24px;
  font-weight: 800;
  color: white;
  letter-spacing: -0.5px;
  display: inline-flex;
  align-items: baseline;
  gap: 6px;
  transition: color 0.25s ease;
}
.price-note {
  font-size: 11px;
  color: #10b981;
  font-weight: 500;
  margin-top: 3px;
}
.price-breakdown-row {
  margin-top: 4px;
  font-size: 11px;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}
.breakdown-pill {
  background: rgba(255, 255, 255, 0.05);
  padding: 2px 7px;
  border-radius: 4px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 10.5px;
}
.reset-specs-btn {
  background: none;
  border: none;
  color: #f87171;
  font-size: 10.5px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: underline;
  padding: 0;
  margin-left: auto;
}
.reset-specs-btn:hover {
  color: #ef4444;
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
  transition: all 0.2s ease;
  opacity: 0.7;
}
.coop-badge-logo:hover, .coop-badge-logo.active-coop {
  opacity: 1;
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
  transform: scale(1.05);
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

        <!-- Interactive Chassis Configurations Selector -->
        <div class="chassis-tags-section">
          <div class="chassis-tags-label">
            <span>Select Chassis Specification</span>
            <span style="font-size: 10px; color: #10b981; font-weight: 600;">Standard &middot; $0 Surcharge</span>
          </div>
          <div class="chassis-selector-list" data-model="MT4H">
            <button type="button" class="chassis-chip-btn active" data-model="MT4H" data-chassis="MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper" onclick="selectChassis('MT4H', this)">
              <div class="chassis-btn-left">
                <span class="chassis-radio-indicator"></span>
                <span class="chassis-label-text">Isuzu NRR Cabover 19,500 GVWR &middot; Left-Side Dump</span>
              </div>
              <span class="chassis-badge-included">Selected</span>
            </button>
            <button type="button" class="chassis-chip-btn" data-model="MT4H" data-chassis="MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - RIGHT side dumping hopper" onclick="selectChassis('MT4H', this)">
              <div class="chassis-btn-left">
                <span class="chassis-radio-indicator"></span>
                <span class="chassis-label-text">Isuzu NRR Cabover 19,500 GVWR &middot; Right-Side Dump</span>
              </div>
              <span class="chassis-badge-included" style="display:none;">Selected</span>
            </button>
            <button type="button" class="chassis-chip-btn" data-model="MT4H" data-chassis="MT4H - Ford F-550XL conventional, 19,000 GVWR, non-CDL - LEFT side dumping hopper" onclick="selectChassis('MT4H', this)">
              <div class="chassis-btn-left">
                <span class="chassis-radio-indicator"></span>
                <span class="chassis-label-text">Ford F-550XL Conventional 19,000 GVWR &middot; Left-Side Dump</span>
              </div>
              <span class="chassis-badge-included" style="display:none;">Selected</span>
            </button>
          </div>
        </div>

        <!-- Procurement / Pricing Contract Selector -->
        <div class="basis-selector-section">
          <div class="basis-selector-label">
            <span>Procurement Pricing Basis</span>
            <span id="mt4hBasisBadge" style="font-size:10px;color:#94a3b8;font-weight:600;">Commercial Baseline</span>
          </div>
          <div class="basis-pills-row" data-model="MT4H">
            <button type="button" class="basis-pill-btn active" data-basis="msrp" data-basis-label="MSRP (Commercial)" onclick="selectPricingBasis('MT4H', 'msrp', this)">
              MSRP Commercial
            </button>
            <button type="button" class="basis-pill-btn" data-basis="sourcewell" data-basis-label="Sourcewell" onclick="selectPricingBasis('MT4H', 'sourcewell', this)">
              Sourcewell (#093021)
            </button>
            <button type="button" class="basis-pill-btn" data-basis="hgac" data-basis-label="H-GAC / HGACBuy" onclick="selectPricingBasis('MT4H', 'hgac', this)">
              H-GAC Buy
            </button>
            <button type="button" class="basis-pill-btn" data-basis="buyboard" data-basis-label="BuyBoard" onclick="selectPricingBasis('MT4H', 'buyboard', this)">
              BuyBoard
            </button>
          </div>
        </div>

        <!-- Interactive Equipment & Factory Upgrades Configurator -->
        <div class="upgrades-configurator-section" id="mt4hUpgradesSection">
          <div class="upgrades-header-bar" onclick="toggleUpgradesSection('MT4H')">
            <div class="upgrades-title-wrap">
              <span>&#9889; Factory Options &amp; Upgrades</span>
              <span class="upgrades-count-pill" id="mt4hUpgradesCount">0 Selected</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
              <button type="button" class="reset-specs-btn" onclick="event.stopPropagation(); resetCardConfig('MT4H');" title="Reset all upgrades to baseline">Reset</button>
              <span class="upgrades-toggle-chevron" id="mt4hUpgradesChevron">&#9660;</span>
            </div>
          </div>
          <div class="upgrades-body" id="mt4hUpgradesBody">
            <label class="upgrade-option-card" data-enh-id="6" data-msrp="13000" data-swnet="13650" data-swlist="13929" data-desc="Stainless Steel Hopper ILO Standard">
              <div class="upgrade-card-left">
                <input type="checkbox" style="display:none;" onchange="toggleOption('MT4H', this)">
                <span class="upgrade-checkbox"></span>
                <span class="upgrade-cat-tag">HOPPER</span>
                <span class="upgrade-name" title="Stainless Steel Hopper ILO Standard">Stainless Steel 304 Hopper Body</span>
              </div>
              <span class="upgrade-price-tag" data-base-msrp="13000">+$13,000</span>
            </label>

            <label class="upgrade-option-card" data-enh-id="10" data-msrp="20400" data-swnet="20400" data-swlist="20816" data-desc="Dualization for MT4H Chassis">
              <div class="upgrade-card-left">
                <input type="checkbox" style="display:none;" onchange="toggleOption('MT4H', this)">
                <span class="upgrade-checkbox"></span>
                <span class="upgrade-cat-tag">STEERING</span>
                <span class="upgrade-name" title="Dualization for MT4H Chassis">Dualized Steering Conversion</span>
              </div>
              <span class="upgrade-price-tag" data-base-msrp="20400">+$20,400</span>
            </label>

            <label class="upgrade-option-card" data-enh-id="2" data-msrp="2293" data-swnet="2454" data-swlist="2504" data-desc="Rear Mounted LED Light Bar">
              <div class="upgrade-card-left">
                <input type="checkbox" style="display:none;" onchange="toggleOption('MT4H', this)">
                <span class="upgrade-checkbox"></span>
                <span class="upgrade-cat-tag">LIGHTS</span>
                <span class="upgrade-name" title="Rear Mounted LED Light Bar">Rear Mounted LED Light Bar</span>
              </div>
              <span class="upgrade-price-tag" data-base-msrp="2293">+$2,293</span>
            </label>

            <label class="upgrade-option-card" data-enh-id="5" data-msrp="1405" data-swnet="1475" data-swlist="1505" data-desc="Elevator System Flush">
              <div class="upgrade-card-left">
                <input type="checkbox" style="display:none;" onchange="toggleOption('MT4H', this)">
                <span class="upgrade-checkbox"></span>
                <span class="upgrade-cat-tag">WATER</span>
                <span class="upgrade-name" title="Elevator System Flush">Elevator System High-Volume Flush</span>
              </div>
              <span class="upgrade-price-tag" data-base-msrp="1405">+$1,405</span>
            </label>

            <div class="more-upgrades-drawer" id="mt4hMoreUpgrades" style="display:none;">
              <label class="upgrade-option-card" data-enh-id="1" data-msrp="529" data-swnet="556" data-swlist="567" data-desc="Front LED Strobe Light (behind cab, visible from front)">
                <div class="upgrade-card-left">
                  <input type="checkbox" style="display:none;" onchange="toggleOption('MT4H', this)">
                  <span class="upgrade-checkbox"></span>
                  <span class="upgrade-cat-tag">LIGHTS</span>
                  <span class="upgrade-name">Front LED Safety Strobe Light</span>
                </div>
                <span class="upgrade-price-tag" data-base-msrp="529">+$529</span>
              </label>

              <label class="upgrade-option-card" data-enh-id="4" data-msrp="746" data-swnet="746" data-swlist="761" data-desc="Strip Main Broom ILO Standard">
                <div class="upgrade-card-left">
                  <input type="checkbox" style="display:none;" onchange="toggleOption('MT4H', this)">
                  <span class="upgrade-checkbox"></span>
                  <span class="upgrade-cat-tag">BROOMS</span>
                  <span class="upgrade-name">Strip Main Broom ILO Standard</span>
                </div>
                <span class="upgrade-price-tag" data-base-msrp="746">+$746</span>
              </label>

              <label class="upgrade-option-card" data-enh-id="13" data-msrp="998" data-swnet="998" data-swlist="1018" data-desc="Pivot Drag Shoe Arms ILO Standard">
                <div class="upgrade-card-left">
                  <input type="checkbox" style="display:none;" onchange="toggleOption('MT4H', this)">
                  <span class="upgrade-checkbox"></span>
                  <span class="upgrade-cat-tag">DRAG SHOES</span>
                  <span class="upgrade-name">Pivot Drag Shoe Arms ILO Standard</span>
                </div>
                <span class="upgrade-price-tag" data-base-msrp="998">+$998</span>
              </label>

              <label class="upgrade-option-card" data-enh-id="11" data-msrp="6106" data-swnet="6106" data-swlist="6231" data-desc="2nd Year Warranty Parts/Labor - Module Only (excl. aux engine)">
                <div class="upgrade-card-left">
                  <input type="checkbox" style="display:none;" onchange="toggleOption('MT4H', this)">
                  <span class="upgrade-checkbox"></span>
                  <span class="upgrade-cat-tag">WARRANTY</span>
                  <span class="upgrade-name">2nd Year Extended Warranty (Module)</span>
                </div>
                <span class="upgrade-price-tag" data-base-msrp="6106">+$6,106</span>
              </label>

              <label class="upgrade-option-card" data-enh-id="12" data-msrp="11554" data-swnet="11554" data-swlist="11790" data-desc="3rd Year Warranty Parts/Labor - Module Only (excl. aux engine)">
                <div class="upgrade-card-left">
                  <input type="checkbox" style="display:none;" onchange="toggleOption('MT4H', this)">
                  <span class="upgrade-checkbox"></span>
                  <span class="upgrade-cat-tag">WARRANTY</span>
                  <span class="upgrade-name">3rd Year Extended Warranty (Module)</span>
                </div>
                <span class="upgrade-price-tag" data-base-msrp="11554">+$11,554</span>
              </label>
            </div>

            <button type="button" class="show-more-options-btn" id="mt4hShowMoreBtn" onclick="toggleMoreUpgrades('MT4H')">
              + View 5 More Factory Upgrades
            </button>
          </div>
        </div>

        <!-- Pricing & Cooperative Contracts -->
        <div class="pricing-coop-bar">
          <div class="price-display-wrap">
            @if ($isManager)
              <span class="price-caption" id="mt4hPriceCaption">Configured Pricing &middot; MSRP Commercial</span>
              <span class="price-amount" id="mt4hPriceAmount">$257,493 <span id="mt4hPriceSuffix" style="font-size:13px;font-weight:normal;color:#94a3b8;">MSRP</span></span>
              <div class="price-breakdown-row" id="mt4hBreakdownRow">
                <span class="breakdown-pill" id="mt4hBreakdownPill">Base: $257,493 &bull; Upgrades: $0</span>
              </div>
              <span class="price-note" id="mt4hManagerBreakdown">&bull; Sourcewell List: $307,378 &bull; Net: $270,493</span>
            @elseif ($isAdmin)
              <span class="price-caption">Master Model Code</span>
              <span class="price-amount" style="font-size: 18px; color: #cbd5e1;">MT4H-SERIES</span>
              <span style="font-size: 11px; color: #94a3b8;">Technical Admin Master &middot; Financials Restricted</span>
            @else
              <span class="price-caption" id="mt4hPriceCaption">Configured Price</span>
              <span class="price-amount" id="mt4hPriceAmount">$257,493 <span id="mt4hPriceSuffix" style="font-size:13px;font-weight:normal;color:#94a3b8;">MSRP</span></span>
              <div class="price-breakdown-row" id="mt4hBreakdownRow">
                <span class="breakdown-pill" id="mt4hBreakdownPill">Base: $257,493 &bull; Upgrades: $0</span>
              </div>
              <span class="price-note" id="mt4hPriceNote">&#10003; Pre-approved Cooperative Pricing Available</span>
            @endif
          </div>

          <div class="coop-logos-row" id="mt4hCoopLogos">
            <img src="{{ asset('images/logo-sourcewell.png') }}" alt="Sourcewell" class="coop-badge-logo" id="mt4hLogoSourcewell" title="Sourcewell Contract #093021-ODR">
            <img src="{{ asset('images/logo-hgac.png') }}" alt="H-GAC" class="coop-badge-logo" id="mt4hLogoHgac" title="H-GAC Pre-Approved">
            <img src="{{ asset('images/logo-buyboard.png') }}" alt="BuyBoard" class="coop-badge-logo" id="mt4hLogoBuyboard" title="BuyBoard National Cooperative">
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="card-cta-bar">
          <a id="mt4hConfigureBtn" href="{{ route('sales.quotes.create') }}?model=MT4H" class="btn-amg-primary">
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

        <!-- Interactive Chassis Configurations Selector -->
        <div class="chassis-tags-section">
          <div class="chassis-tags-label">
            <span>Select Chassis Specification</span>
            <span style="font-size: 10px; color: #10b981; font-weight: 600;">Standard &middot; $0 Surcharge</span>
          </div>
          <div class="chassis-selector-list" data-model="MS5H">
            <button type="button" class="chassis-chip-btn active" data-model="MS5H" data-chassis="MS5H - Conventional Class 6, 25,500 GVWR, 148&quot; WB, 300HP Cummins, non-CDL" onclick="selectChassis('MS5H', this)">
              <div class="chassis-btn-left">
                <span class="chassis-radio-indicator"></span>
                <span class="chassis-label-text">Conventional Class 6 &middot; 25,500 GVWR &middot; Non-CDL Operation</span>
              </div>
              <span class="chassis-badge-included">Selected</span>
            </button>
            <button type="button" class="chassis-chip-btn" data-model="MS5H" data-chassis="MS5H - Conventional Class 7, CDL-required configuration, 300HP Cummins - no price difference" onclick="selectChassis('MS5H', this)">
              <div class="chassis-btn-left">
                <span class="chassis-radio-indicator"></span>
                <span class="chassis-label-text">Conventional Class 7 &middot; 26,000+ GVWR &middot; CDL-Required Heavy Duty</span>
              </div>
              <span class="chassis-badge-included" style="display:none;">Selected</span>
            </button>
          </div>
        </div>

        <!-- Procurement / Pricing Contract Selector -->
        <div class="basis-selector-section">
          <div class="basis-selector-label">
            <span>Procurement Pricing Basis</span>
            <span id="ms5hBasisBadge" style="font-size:10px;color:#94a3b8;font-weight:600;">Commercial Baseline</span>
          </div>
          <div class="basis-pills-row" data-model="MS5H">
            <button type="button" class="basis-pill-btn active" data-basis="msrp" data-basis-label="MSRP (Commercial)" onclick="selectPricingBasis('MS5H', 'msrp', this)">
              MSRP Commercial
            </button>
            <button type="button" class="basis-pill-btn" data-basis="sourcewell" data-basis-label="Sourcewell" onclick="selectPricingBasis('MS5H', 'sourcewell', this)">
              Sourcewell (#093021)
            </button>
            <button type="button" class="basis-pill-btn" data-basis="hgac" data-basis-label="H-GAC / HGACBuy" onclick="selectPricingBasis('MS5H', 'hgac', this)">
              H-GAC Buy
            </button>
            <button type="button" class="basis-pill-btn" data-basis="buyboard" data-basis-label="BuyBoard" onclick="selectPricingBasis('MS5H', 'buyboard', this)">
              BuyBoard
            </button>
          </div>
        </div>

        <!-- Interactive Equipment & Factory Upgrades Configurator -->
        <div class="upgrades-configurator-section" id="ms5hUpgradesSection">
          <div class="upgrades-header-bar" onclick="toggleUpgradesSection('MS5H')">
            <div class="upgrades-title-wrap">
              <span>&#9889; Factory Options &amp; Upgrades</span>
              <span class="upgrades-count-pill" id="ms5hUpgradesCount">0 Selected</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
              <button type="button" class="reset-specs-btn" onclick="event.stopPropagation(); resetCardConfig('MS5H');" title="Reset all upgrades to baseline">Reset</button>
              <span class="upgrades-toggle-chevron" id="ms5hUpgradesChevron">&#9660;</span>
            </div>
          </div>
          <div class="upgrades-body" id="ms5hUpgradesBody">
            <label class="upgrade-option-card" data-enh-id="20" data-msrp="19519" data-swnet="19519" data-swlist="19918" data-desc="Dualized Conversion on Chassis">
              <div class="upgrade-card-left">
                <input type="checkbox" style="display:none;" onchange="toggleOption('MS5H', this)">
                <span class="upgrade-checkbox"></span>
                <span class="upgrade-cat-tag">STEERING</span>
                <span class="upgrade-name" title="Dualized Conversion on Chassis">Dualized Steering Conversion (Freightliner)</span>
              </div>
              <span class="upgrade-price-tag" data-base-msrp="19519">+$19,519</span>
            </label>

            <label class="upgrade-option-card" data-enh-id="15" data-msrp="2293" data-swnet="2454" data-swlist="2504" data-desc="Rear Mounted LED Light Bar">
              <div class="upgrade-card-left">
                <input type="checkbox" style="display:none;" onchange="toggleOption('MS5H', this)">
                <span class="upgrade-checkbox"></span>
                <span class="upgrade-cat-tag">LIGHTS</span>
                <span class="upgrade-name" title="Rear Mounted LED Light Bar">Rear Mounted LED Light Bar</span>
              </div>
              <span class="upgrade-price-tag" data-base-msrp="2293">+$2,293</span>
            </label>

            <label class="upgrade-option-card" data-enh-id="18" data-msrp="1405" data-swnet="1475" data-swlist="1505" data-desc="Elevator System Flush">
              <div class="upgrade-card-left">
                <input type="checkbox" style="display:none;" onchange="toggleOption('MS5H', this)">
                <span class="upgrade-checkbox"></span>
                <span class="upgrade-cat-tag">WATER</span>
                <span class="upgrade-name" title="Elevator System Flush">Elevator System High-Volume Flush</span>
              </div>
              <span class="upgrade-price-tag" data-base-msrp="1405">+$1,405</span>
            </label>

            <label class="upgrade-option-card" data-enh-id="14" data-msrp="529" data-swnet="556" data-swlist="567" data-desc="Front LED Strobe Light (behind cab, visible from front)">
              <div class="upgrade-card-left">
                <input type="checkbox" style="display:none;" onchange="toggleOption('MS5H', this)">
                <span class="upgrade-checkbox"></span>
                <span class="upgrade-cat-tag">LIGHTS</span>
                <span class="upgrade-name">Front LED Safety Strobe Light</span>
              </div>
              <span class="upgrade-price-tag" data-base-msrp="529">+$529</span>
            </label>

            <div class="more-upgrades-drawer" id="ms5hMoreUpgrades" style="display:none;">
              <label class="upgrade-option-card" data-enh-id="17" data-msrp="746" data-swnet="746" data-swlist="761" data-desc="Strip Main Broom ILO Standard">
                <div class="upgrade-card-left">
                  <input type="checkbox" style="display:none;" onchange="toggleOption('MS5H', this)">
                  <span class="upgrade-checkbox"></span>
                  <span class="upgrade-cat-tag">BROOMS</span>
                  <span class="upgrade-name">Strip Main Broom ILO Standard</span>
                </div>
                <span class="upgrade-price-tag" data-base-msrp="746">+$746</span>
              </label>

              <label class="upgrade-option-card" data-enh-id="23" data-msrp="998" data-swnet="998" data-swlist="1018" data-desc="Pivot Drag Shoe Arms ILO Standard">
                <div class="upgrade-card-left">
                  <input type="checkbox" style="display:none;" onchange="toggleOption('MS5H', this)">
                  <span class="upgrade-checkbox"></span>
                  <span class="upgrade-cat-tag">DRAG SHOES</span>
                  <span class="upgrade-name">Pivot Drag Shoe Arms ILO Standard</span>
                </div>
                <span class="upgrade-price-tag" data-base-msrp="998">+$998</span>
              </label>

              <label class="upgrade-option-card" data-enh-id="21" data-msrp="6106" data-swnet="6106" data-swlist="6231" data-desc="2nd Year Warranty Parts/Labor - Module Only (excl. chassis)">
                <div class="upgrade-card-left">
                  <input type="checkbox" style="display:none;" onchange="toggleOption('MS5H', this)">
                  <span class="upgrade-checkbox"></span>
                  <span class="upgrade-cat-tag">WARRANTY</span>
                  <span class="upgrade-name">2nd Year Extended Warranty (Module)</span>
                </div>
                <span class="upgrade-price-tag" data-base-msrp="6106">+$6,106</span>
              </label>

              <label class="upgrade-option-card" data-enh-id="22" data-msrp="11554" data-swnet="11554" data-swlist="11790" data-desc="3rd Year Warranty Parts/Labor - Module Only (excl. chassis)">
                <div class="upgrade-card-left">
                  <input type="checkbox" style="display:none;" onchange="toggleOption('MS5H', this)">
                  <span class="upgrade-checkbox"></span>
                  <span class="upgrade-cat-tag">WARRANTY</span>
                  <span class="upgrade-name">3rd Year Extended Warranty (Module)</span>
                </div>
                <span class="upgrade-price-tag" data-base-msrp="11554">+$11,554</span>
              </label>
            </div>

            <button type="button" class="show-more-options-btn" id="ms5hShowMoreBtn" onclick="toggleMoreUpgrades('MS5H')">
              + View 4 More Factory Upgrades
            </button>
          </div>
        </div>

        <!-- Pricing & Cooperative Contracts -->
        <div class="pricing-coop-bar">
          <div class="price-display-wrap">
            @if ($isManager)
              <span class="price-caption" id="ms5hPriceCaption">Configured Pricing &middot; MSRP Commercial</span>
              <span class="price-amount" id="ms5hPriceAmount">$349,149 <span id="ms5hPriceSuffix" style="font-size:13px;font-weight:normal;color:#94a3b8;">MSRP</span></span>
              <div class="price-breakdown-row" id="ms5hBreakdownRow">
                <span class="breakdown-pill" id="ms5hBreakdownPill">Base: $349,149 &bull; Upgrades: $0</span>
              </div>
              <span class="price-note" id="ms5hManagerBreakdown">&bull; Sourcewell List: $411,533 &bull; Net: $362,149</span>
            @elseif ($isAdmin)
              <span class="price-caption">Master Model Code</span>
              <span class="price-amount" style="font-size: 18px; color: #cbd5e1;">MS5H-SERIES</span>
              <span style="font-size: 11px; color: #94a3b8;">Technical Admin Master &middot; Financials Restricted</span>
            @else
              <span class="price-caption" id="ms5hPriceCaption">Configured Price</span>
              <span class="price-amount" id="ms5hPriceAmount">$349,149 <span id="ms5hPriceSuffix" style="font-size:13px;font-weight:normal;color:#94a3b8;">MSRP</span></span>
              <div class="price-breakdown-row" id="ms5hBreakdownRow">
                <span class="breakdown-pill" id="ms5hBreakdownPill">Base: $349,149 &bull; Upgrades: $0</span>
              </div>
              <span class="price-note" id="ms5hPriceNote">&#10003; Pre-approved Cooperative Pricing Available</span>
            @endif
          </div>

          <div class="coop-logos-row" id="ms5hCoopLogos">
            <img src="{{ asset('images/logo-sourcewell.png') }}" alt="Sourcewell" class="coop-badge-logo" id="ms5hLogoSourcewell" title="Sourcewell Contract #093021-ODR">
            <img src="{{ asset('images/logo-hgac.png') }}" alt="H-GAC" class="coop-badge-logo" id="ms5hLogoHgac" title="H-GAC Pre-Approved">
            <img src="{{ asset('images/logo-buyboard.png') }}" alt="BuyBoard" class="coop-badge-logo" id="ms5hLogoBuyboard" title="BuyBoard National Cooperative">
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="card-cta-bar">
          <a id="ms5hConfigureBtn" href="{{ route('sales.quotes.create') }}?model=MS5H" class="btn-amg-primary">
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
// Configurator State Store
const CARD_CONFIGS = {
  MT4H: {
    bases: {
      msrp: { amount: 257493, list: 257493, label: 'MSRP Commercial' },
      sourcewell: { amount: 270493, list: 307378, label: 'Sourcewell' },
      hgac: { amount: 270493, list: 307378, label: 'H-GAC / HGACBuy' },
      buyboard: { amount: 270493, list: 307378, label: 'BuyBoard' }
    },
    basis: 'msrp',
    chassis: 'MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper',
    selected: new Map(),
    currentTotal: 257493
  },
  MS5H: {
    bases: {
      msrp: { amount: 349149, list: 349149, label: 'MSRP Commercial' },
      sourcewell: { amount: 362149, list: 411533, label: 'Sourcewell' },
      hgac: { amount: 362149, list: 411533, label: 'H-GAC / HGACBuy' },
      buyboard: { amount: 362149, list: 411533, label: 'BuyBoard' }
    },
    basis: 'msrp',
    chassis: 'MS5H - Conventional Class 6, 25,500 GVWR, 148" WB, 300HP Cummins, non-CDL',
    selected: new Map(),
    currentTotal: 349149
  }
};

const IS_MANAGER = {{ $isManager ? 'true' : 'false' }};
const IS_ADMIN = {{ $isAdmin ? 'true' : 'false' }};

function fmtPrice(n) {
  return '$' + Math.round(n).toLocaleString('en-US');
}

// Select Chassis Specification
function selectChassis(modelCode, btnEl) {
  const container = document.querySelector(`.chassis-selector-list[data-model="${modelCode}"]`);
  if (!container) return;
  container.querySelectorAll('.chassis-chip-btn').forEach(b => {
    b.classList.remove('active');
    const badge = b.querySelector('.chassis-badge-included');
    if (badge) badge.style.display = 'none';
  });

  btnEl.classList.add('active');
  const badge = btnEl.querySelector('.chassis-badge-included');
  if (badge) badge.style.display = '';

  CARD_CONFIGS[modelCode].chassis = btnEl.getAttribute('data-chassis');
  updateConfigureUrl(modelCode);
}

// Select Procurement / Pricing Contract Basis
function selectPricingBasis(modelCode, basisKey, btnEl) {
  const cfg = CARD_CONFIGS[modelCode];
  cfg.basis = basisKey;

  const row = document.querySelector(`.basis-pills-row[data-model="${modelCode}"]`);
  if (row) {
    row.querySelectorAll('.basis-pill-btn').forEach(b => b.classList.remove('active'));
    btnEl.classList.add('active');
  }

  // Update basis badge
  const basisBadge = document.getElementById(modelCode.toLowerCase() + 'BasisBadge');
  if (basisBadge) {
    basisBadge.textContent = cfg.bases[basisKey].label;
  }

  // Update coop logos active glow
  const logosWrap = document.getElementById(modelCode.toLowerCase() + 'CoopLogos');
  if (logosWrap) {
    logosWrap.querySelectorAll('.coop-badge-logo').forEach(l => l.classList.remove('active-coop'));
    if (basisKey === 'sourcewell') document.getElementById(modelCode.toLowerCase() + 'LogoSourcewell')?.classList.add('active-coop');
    if (basisKey === 'hgac') document.getElementById(modelCode.toLowerCase() + 'LogoHgac')?.classList.add('active-coop');
    if (basisKey === 'buyboard') document.getElementById(modelCode.toLowerCase() + 'LogoBuyboard')?.classList.add('active-coop');
  }

  // Update visible upgrade price tags based on basis
  const cardSection = document.getElementById(modelCode.toLowerCase() + 'UpgradesSection');
  if (cardSection) {
    const isCoop = basisKey !== 'msrp';
    cardSection.querySelectorAll('.upgrade-option-card').forEach(card => {
      const msrp = parseFloat(card.getAttribute('data-msrp')) || 0;
      const swnet = parseFloat(card.getAttribute('data-swnet')) || msrp;
      const priceTag = card.querySelector('.upgrade-price-tag');
      if (priceTag) {
        priceTag.textContent = '+' + fmtPrice(isCoop ? swnet : msrp);
      }
    });
  }

  recalculateCardPrice(modelCode);
}

// Toggle Factory Upgrade Option
function toggleOption(modelCode, inputEl) {
  const card = inputEl.closest('.upgrade-option-card');
  if (!card) return;

  const desc = card.getAttribute('data-desc');
  const msrp = parseFloat(card.getAttribute('data-msrp')) || 0;
  const swNet = parseFloat(card.getAttribute('data-swnet')) || msrp;
  const swList = parseFloat(card.getAttribute('data-swlist')) || msrp;

  if (inputEl.checked) {
    card.classList.add('selected');
    CARD_CONFIGS[modelCode].selected.set(desc, { msrp, swNet, swList });
  } else {
    card.classList.remove('selected');
    CARD_CONFIGS[modelCode].selected.delete(desc);
  }

  recalculateCardPrice(modelCode);
}

// Direct toggle by clicking anywhere on upgrade-option-card
document.addEventListener('click', (e) => {
  const card = e.target.closest('.upgrade-option-card');
  if (!card) return;
  if (e.target.tagName === 'INPUT') return; // Handled by change event

  const input = card.querySelector('input[type="checkbox"]');
  if (input) {
    input.checked = !input.checked;
    const model = card.closest('[data-model]')?.getAttribute('data-model') ||
                 (card.closest('#mt4hUpgradesSection') ? 'MT4H' : 'MS5H');
    toggleOption(model, input);
  }
});

// Toggle Section Accordion
function toggleUpgradesSection(modelCode) {
  const body = document.getElementById(modelCode.toLowerCase() + 'UpgradesBody');
  const chevron = document.getElementById(modelCode.toLowerCase() + 'UpgradesChevron');
  if (!body) return;
  if (body.style.display === 'none') {
    body.style.display = 'flex';
    if (chevron) chevron.innerHTML = '&#9660;';
  } else {
    body.style.display = 'none';
    if (chevron) chevron.innerHTML = '&#9654;';
  }
}

// Toggle More Upgrades Drawer
function toggleMoreUpgrades(modelCode) {
  const drawer = document.getElementById(modelCode.toLowerCase() + 'MoreUpgrades');
  const btn = document.getElementById(modelCode.toLowerCase() + 'ShowMoreBtn');
  if (!drawer || !btn) return;

  if (drawer.style.display === 'none') {
    drawer.style.display = 'flex';
    btn.textContent = '- Collapse Additional Upgrades';
  } else {
    drawer.style.display = 'none';
    const totalCount = modelCode === 'MT4H' ? '5' : '4';
    btn.textContent = `+ View ${totalCount} More Factory Upgrades`;
  }
}

// Reset Card Configuration to Baseline
function resetCardConfig(modelCode) {
  const cfg = CARD_CONFIGS[modelCode];
  cfg.selected.clear();

  // Reset checkboxes and card styling
  const section = document.getElementById(modelCode.toLowerCase() + 'UpgradesSection');
  if (section) {
    section.querySelectorAll('.upgrade-option-card').forEach(c => {
      c.classList.remove('selected');
      const chk = c.querySelector('input[type="checkbox"]');
      if (chk) chk.checked = false;
    });
  }

  // Reset basis to msrp
  const firstBasisBtn = document.querySelector(`.basis-pills-row[data-model="${modelCode}"] .basis-pill-btn`);
  if (firstBasisBtn) selectPricingBasis(modelCode, 'msrp', firstBasisBtn);

  // Reset chassis to first
  const firstChassisBtn = document.querySelector(`.chassis-selector-list[data-model="${modelCode}"] .chassis-chip-btn`);
  if (firstChassisBtn) selectChassis(modelCode, firstChassisBtn);

  recalculateCardPrice(modelCode);
}

// Recalculate Card Price with Animated CountUp
function recalculateCardPrice(modelCode) {
  const cfg = CARD_CONFIGS[modelCode];
  const basisData = cfg.bases[cfg.basis];
  const isCoop = cfg.basis !== 'msrp';

  // Calculate sum of active enhancements
  let enhMSRP = 0;
  let enhSWNet = 0;
  let enhSWList = 0;

  cfg.selected.forEach(val => {
    enhMSRP += val.msrp;
    enhSWNet += val.swNet;
    enhSWList += val.swList;
  });

  const selectedCount = cfg.selected.size;
  const activeEnhSum = isCoop ? enhSWNet : enhMSRP;
  const newTargetTotal = basisData.amount + activeEnhSum;

  // Update Upgrades count pill
  const countPill = document.getElementById(modelCode.toLowerCase() + 'UpgradesCount');
  if (countPill) {
    countPill.textContent = selectedCount === 0 
      ? '0 Selected' 
      : `${selectedCount} Selected (+${fmtPrice(activeEnhSum)})`;
  }

  // Update Breakdown pill
  const breakdownPill = document.getElementById(modelCode.toLowerCase() + 'BreakdownPill');
  if (breakdownPill) {
    const basisName = isCoop ? cfg.bases[cfg.basis].label : 'Base';
    breakdownPill.innerHTML = `${basisName}: <strong>${fmtPrice(basisData.amount)}</strong> &bull; Upgrades (${selectedCount}): <strong>${fmtPrice(activeEnhSum)}</strong>`;
  }

  // Update Price Caption
  const priceCaption = document.getElementById(modelCode.toLowerCase() + 'PriceCaption');
  if (priceCaption) {
    priceCaption.textContent = isCoop 
      ? `Configured ${basisData.label} Price` 
      : 'Configured MSRP Price';
  }

  // Animate main price amount
  const priceAmountEl = document.getElementById(modelCode.toLowerCase() + 'PriceAmount');
  if (priceAmountEl && !IS_ADMIN) {
    animateCount(priceAmountEl, cfg.currentTotal, newTargetTotal, isCoop ? 'NET' : 'MSRP');
  }
  cfg.currentTotal = newTargetTotal;

  // Update Manager cooperative breakdown if present
  if (IS_MANAGER) {
    const mgrBreakdownEl = document.getElementById(modelCode.toLowerCase() + 'ManagerBreakdown');
    if (mgrBreakdownEl) {
      const swListTotal = fmtPrice(cfg.bases.sourcewell.list + enhSWList);
      const swNetTotal = fmtPrice(cfg.bases.sourcewell.amount + enhSWNet);
      mgrBreakdownEl.innerHTML = `&bull; Sourcewell List: ${swListTotal} &bull; Net: ${swNetTotal}`;
    }
  }

  updateConfigureUrl(modelCode);
}

// Smooth number counting animation
function animateCount(targetEl, startVal, endVal, suffix) {
  if (startVal === endVal) return;
  const duration = 400; // 0.4s quick responsive count
  const startTime = performance.now();

  function step(time) {
    const elapsed = time - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const easeOut = 1 - Math.pow(1 - progress, 3);
    const current = Math.round(startVal + (endVal - startVal) * easeOut);

    targetEl.innerHTML = `${fmtPrice(current)} <span style="font-size:13px;font-weight:normal;color:#94a3b8;">${suffix}</span>`;

    if (progress < 1) {
      requestAnimationFrame(step);
    } else {
      targetEl.innerHTML = `${fmtPrice(endVal)} <span style="font-size:13px;font-weight:normal;color:#94a3b8;">${suffix}</span>`;
    }
  }

  requestAnimationFrame(step);
}

// Update Configure in Quote Builder link URL
function updateConfigureUrl(modelCode) {
  const cfg = CARD_CONFIGS[modelCode];
  const btn = document.getElementById(modelCode.toLowerCase() + 'ConfigureBtn');
  if (!btn) return;

  const params = new URLSearchParams();
  params.set('model', modelCode);
  if (cfg.chassis) params.set('chassis', cfg.chassis);
  if (cfg.bases[cfg.basis]) params.set('basis', cfg.bases[cfg.basis].label);

  const selectedArr = Array.from(cfg.selected.keys());
  if (selectedArr.length > 0) {
    params.set('enhancements', selectedArr.join('||'));
  }

  btn.href = `{{ route('sales.quotes.create') }}?${params.toString()}`;

  // Also update modal configure button if modal is open for this model
  const modalBtn = document.getElementById('modalConfigureBtn');
  if (modalBtn && document.getElementById('specModal')?.classList.contains('open')) {
    modalBtn.href = btn.href;
  }
}

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
      { label: 'Front LED Strobe Light', val: 'Mounted behind cab, visible from front ($529 MSRP)', desc: 'Front LED Strobe Light (behind cab, visible from front)' },
      { label: 'Rear Mounted LED Light Bar', val: 'High-intensity multi-pattern traffic director ($2,293 MSRP)', desc: 'Rear Mounted LED Light Bar' },
      { label: 'Strip Main Broom ILO Standard', val: 'Heavy-duty chevron strip broom ($746 MSRP)', desc: 'Strip Main Broom ILO Standard' },
      { label: 'Elevator System Flush', val: 'Quick-connect water purge system ($1,405 MSRP)', desc: 'Elevator System Flush' },
      { label: 'Stainless Steel Hopper', val: '304 stainless steel hopper body replacement ($13,000 MSRP)', desc: 'Stainless Steel Hopper ILO Standard' },
      { label: 'Dualization for MT4H Chassis', val: 'Dual steering column, pedals & gauge cluster ($20,400 MSRP)', desc: 'Dualization for MT4H Chassis' },
      { label: 'Extended Warranty', val: '2nd Year ($6,106) / 3rd Year ($11,554) Module Parts & Labor', desc: '2nd Year Warranty Parts/Labor - Module Only (excl. aux engine)' }
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
      { label: 'Front LED Strobe Light', val: 'Mounted behind cab, visible from front ($529 MSRP)', desc: 'Front LED Strobe Light (behind cab, visible from front)' },
      { label: 'Rear Mounted LED Light Bar', val: 'High-intensity multi-pattern traffic director ($2,293 MSRP)', desc: 'Rear Mounted LED Light Bar' },
      { label: 'Strip Main Broom ILO Standard', val: 'Chevron pattern high-density sweeping core ($746 MSRP)', desc: 'Strip Main Broom ILO Standard' },
      { label: 'Elevator System Flush', val: 'Quick-connect water purge manifold ($1,405 MSRP)', desc: 'Elevator System Flush' },
      { label: 'Dualized Conversion on Chassis', val: 'Dual steering column & pedals on Freightliner ($19,519 MSRP)', desc: 'Dualized Conversion on Chassis' },
      { label: 'Drag Shoes Pivot Arms', val: 'Pivot drag shoe arms ILO standard ($998 MSRP)', desc: 'Pivot Drag Shoe Arms ILO Standard' },
      { label: 'Extended Warranty', val: '2nd Year ($6,106) / 3rd Year ($11,554) Module Parts & Labor', desc: '2nd Year Warranty Parts/Labor - Module Only (excl. chassis)' }
    ]
  }
};

let currentModalModel = 'MT4H';

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
  currentModalModel = modelCode;
  const data = SPEC_DATA[modelCode];
  if (!data) return;

  document.getElementById('modalTitle').innerHTML = data.title;
  updateConfigureUrl(modelCode);

  renderSpecsTable('specsDimensionsTable', data.dimensions);
  renderSpecsTable('specsPowertrainTable', data.powertrain);
  renderSpecsTable('specsSweepingTable', data.sweeping);
  renderSpecsTable('specsWaterTable', data.water);
  renderModalEnhancements(modelCode, data.enhancements);

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

function renderModalEnhancements(modelCode, enhancements) {
  const tbody = document.getElementById('specsEnhancementsTable');
  if (!tbody) return;

  const cfg = CARD_CONFIGS[modelCode];

  tbody.innerHTML = enhancements.map(enh => {
    const isChecked = cfg.selected.has(enh.desc);
    return `
      <tr>
        <td style="width:50%;">
          <strong>${enh.label}</strong>
          <div style="font-size:11px;color:#94a3b8;margin-top:2px;">${enh.val}</div>
        </td>
        <td style="text-align:right;">
          <button type="button" 
                  class="btn-amg-secondary" 
                  style="padding:5px 12px;font-size:11.5px;${isChecked ? 'background:#10b981;border-color:#10b981;color:#fff;' : ''}" 
                  onclick="toggleModalEnhancement('${modelCode}', '${enh.desc.replace(/'/g, "\\'")}', this)">
            ${isChecked ? '&#10003; Added in Config' : '+ Add to Config'}
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

function toggleModalEnhancement(modelCode, desc, btnEl) {
  const cfg = CARD_CONFIGS[modelCode];
  const section = document.getElementById(modelCode.toLowerCase() + 'UpgradesSection');
  const card = section?.querySelector(`.upgrade-option-card[data-desc="${desc}"]`);
  const input = card?.querySelector('input[type="checkbox"]');

  if (input) {
    input.checked = !input.checked;
    toggleOption(modelCode, input);
  } else {
    // If not directly in card DOM
    if (cfg.selected.has(desc)) {
      cfg.selected.delete(desc);
    } else {
      cfg.selected.set(desc, { msrp: 1000, swNet: 1000, swList: 1000 });
    }
    recalculateCardPrice(modelCode);
  }

  // Refresh button state
  const isChecked = cfg.selected.has(desc);
  btnEl.innerHTML = isChecked ? '&#10003; Added in Config' : '+ Add to Config';
  btnEl.style.background = isChecked ? '#10b981' : '';
  btnEl.style.borderColor = isChecked ? '#10b981' : '';
  btnEl.style.color = isChecked ? '#fff' : '';
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
