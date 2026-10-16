/**
 * Mercedes-Benz AMG Style Animation Engine & Interactivity Script
 * ODRA Road Sweepers Sales Portal
 */

document.addEventListener('DOMContentLoaded', () => {
  initCountUp();
  initScrollAnimations();
  initTableInteractivity();
});

/**
 * Animated number count-up effect
 */
function initCountUp() {
  const counterElements = document.querySelectorAll('.amg-counter[data-target]');
  if (!counterElements.length) return;

  counterElements.forEach(el => {
    const target = parseFloat(el.getAttribute('data-target')) || 0;
    const isCurrency = el.getAttribute('data-currency') === 'true';
    const duration = 1200; // 1.2s animation
    const startTime = performance.now();

    function updateCounter(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      
      // Smooth cubic ease-out
      const easeOut = 1 - Math.pow(1 - progress, 3);
      const current = target * easeOut;

      if (isCurrency) {
        el.textContent = '$' + Math.floor(current).toLocaleString();
      } else if (Number.isInteger(target)) {
        el.textContent = Math.floor(current).toString();
      } else {
        el.textContent = current.toFixed(1);
      }

      if (progress < 1) {
        requestAnimationFrame(updateCounter);
      } else {
        if (isCurrency) {
          el.textContent = '$' + Math.round(target).toLocaleString();
        } else {
          el.textContent = target.toString();
        }
      }
    }

    requestAnimationFrame(updateCounter);
  });
}

/**
 * Scroll reveal animations using Intersection Observer
 */
function initScrollAnimations() {
  const elements = document.querySelectorAll('.amg-reveal');
  if (!elements.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('amg-slide-up');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  elements.forEach(el => observer.observe(el));
}

/**
 * Table client-side search, status pill filter, and KPI card click filtering
 */
function initTableInteractivity() {
  const searchInput = document.getElementById('quoteTableSearch');
  const tableRows = document.querySelectorAll('#recentQuotesTable tbody tr[data-status]');
  const filterPills = document.querySelectorAll('.quote-filter-pill');
  const countIndicator = document.getElementById('filteredQuotesCount');

  let currentStatus = 'all';
  let currentSearch = '';

  function applyFilters() {
    let visibleCount = 0;

    tableRows.forEach(row => {
      const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
      const rowText = row.textContent.toLowerCase();

      const matchesStatus = (currentStatus === 'all') || (rowStatus === currentStatus);
      const matchesSearch = !currentSearch || rowText.includes(currentSearch);

      if (matchesStatus && matchesSearch) {
        row.style.display = '';
        row.classList.add('amg-fade-in');
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (countIndicator) {
      countIndicator.textContent = `Showing ${visibleCount} of ${tableRows.length} quotes`;
    }

    const noResultsRow = document.getElementById('noQuotesMatchRow');
    if (noResultsRow) {
      noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
    }
  }

  // Search input handler
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      currentSearch = e.target.value.toLowerCase().trim();
      applyFilters();
    });
  }

  // Filter pills handler
  filterPills.forEach(pill => {
    pill.addEventListener('click', () => {
      filterPills.forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      currentStatus = pill.getAttribute('data-filter') || 'all';
      applyFilters();
    });
  });

  // KPI card click-to-filter triggers
  const kpiCards = document.querySelectorAll('.kpi-trigger[data-kpi-filter]');
  kpiCards.forEach(card => {
    card.addEventListener('click', () => {
      const filterTarget = card.getAttribute('data-kpi-filter');
      currentStatus = filterTarget;

      // Update pills
      filterPills.forEach(p => {
        if (p.getAttribute('data-filter') === filterTarget) {
          p.classList.add('active');
        } else {
          p.classList.remove('active');
        }
      });

      applyFilters();

      // Smooth scroll down to the table
      const tableSection = document.getElementById('quotesTableCard');
      if (tableSection) {
        tableSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
}

/**
 * Quick Quote Inspect Modal Handler (Async API Fetch)
 */
window.inspectQuote = async function(quoteId) {
  const modal = document.getElementById('quickQuoteModal');
  if (!modal) return;

  const loadingSpinner = document.getElementById('modalLoadingSpinner');
  const modalBodyContent = document.getElementById('modalBodyContent');
  const modalErrorBox = document.getElementById('modalErrorBox');

  // Reset display
  if (loadingSpinner) loadingSpinner.style.display = 'flex';
  if (modalBodyContent) modalBodyContent.style.display = 'none';
  if (modalErrorBox) modalErrorBox.style.display = 'none';

  modal.classList.add('open');
  document.body.style.overflow = 'hidden';

  try {
    const response = await fetch(`/api/quotes/${quoteId}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      }
    });

    if (!response.ok) {
      throw new Error(`Server returned HTTP ${response.status} (${response.statusText})`);
    }

    const data = await response.json();
    const snap = data.snapshot || {};

    // Header info
    const numEl = document.getElementById('modalQuoteNumber');
    if (numEl) numEl.textContent = `Quote Q-${data.quote_number || quoteId}`;

    const verEl = document.getElementById('modalQuoteVersion');
    if (verEl) verEl.textContent = `v${data.version || 1}`;

    const statusEl = document.getElementById('modalQuoteStatus');
    if (statusEl) {
      statusEl.textContent = data.status || 'Draft';
      statusEl.className = 'badge ' + (data.status === 'Finalized' ? 'badge-status-finalized' : 'badge-status-draft');
    }

    const dateEl = document.getElementById('modalQuoteDates');
    if (dateEl) {
      const createdStr = data.created_at ? `Created: ${data.created_at}` : '';
      const updatedStr = data.updated_at ? `Last Updated: ${data.updated_at}` : '';
      dateEl.textContent = [createdStr, updatedStr].filter(Boolean).join('  •  ');
    }

    // Customer details
    const custEl = document.getElementById('modalQuoteCustomer');
    if (custEl) custEl.textContent = snap.customer_company || 'Unassigned / Untitled Customer';

    const contactEl = document.getElementById('modalQuoteContact');
    if (contactEl) contactEl.textContent = snap.customer_contact || 'Direct Purchasing';

    const addrEl = document.getElementById('modalQuoteAddress');
    if (addrEl) {
      const fullAddr = [snap.customer_address_1, snap.customer_address_2].filter(Boolean).join(', ');
      addrEl.textContent = fullAddr || 'Standard Delivery Location';
    }

    const poEl = document.getElementById('modalQuotePo');
    if (poEl) poEl.textContent = snap.po_number || 'None specified';

    // Equipment details
    const modelEl = document.getElementById('modalQuoteModel');
    if (modelEl) modelEl.textContent = `ODRA ${snap.model_code || 'MT4H'} Sweeper`;

    const basisEl = document.getElementById('modalQuoteBasis');
    if (basisEl) basisEl.textContent = snap.pricing_basis || 'MSRP (Commercial)';

    const chassisEl = document.getElementById('modalQuoteChassis');
    if (chassisEl) chassisEl.textContent = snap.chassis_configuration || 'Standard Chassis Configuration';

    // Enhancements / line items
    const enhListEl = document.getElementById('modalQuoteEnhancementsList');
    if (enhListEl) {
      enhListEl.innerHTML = '';
      const items = Array.isArray(snap.enhancements_snapshot) 
        ? snap.enhancements_snapshot.filter(it => it && (it.desc || (it.qty && it.qty > 0)))
        : [];

      if (items.length > 0) {
        items.forEach(it => {
          const row = document.createElement('div');
          row.style.cssText = 'display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:12.5px;';
          const qty = it.qty || 1;
          const desc = it.desc || 'Optional Enhancement';
          const unit = it.unit || it.msrp || 0;
          const ext = it.ext || (qty * unit);
          row.innerHTML = `
            <div style="color:#e2e8f0;">
              <strong>${qty}x</strong> ${desc}
            </div>
            <div style="font-weight:600;color:#cbd5e1;">$${Number(ext).toLocaleString()}</div>
          `;
          enhListEl.appendChild(row);
        });
      } else {
        enhListEl.innerHTML = '<div style="color:#94a3b8;font-style:italic;font-size:12.5px;padding:4px 0;">Standard factory build (no additional optional enhancements selected).</div>';
      }
    }

    // Financial breakdown
    const baseUnitEl = document.getElementById('modalQuoteBaseUnit');
    if (baseUnitEl) baseUnitEl.textContent = `$${Number(snap.base_unit_price || 0).toLocaleString()}`;

    const subtotalEl = document.getElementById('modalQuoteSubtotal');
    if (subtotalEl) subtotalEl.textContent = `$${Number(snap.subtotal || 0).toLocaleString()}`;

    const freightEl = document.getElementById('modalQuoteFreight');
    if (freightEl) freightEl.textContent = `$${Number(snap.freight_amount || 0).toLocaleString()}`;

    const fobEl = document.getElementById('modalQuoteFob');
    if (fobEl) fobEl.textContent = `(${snap.fob_terms || 'Transport estimate'})`;

    const totalEl = document.getElementById('modalQuoteTotal');
    if (totalEl) totalEl.textContent = `$${Number(snap.total || 0).toLocaleString()}`;

    // Action buttons
    const viewBtn = document.getElementById('modalQuoteViewBtn');
    if (viewBtn) {
      viewBtn.href = data.view_url || `/sales/quotes/${quoteId}`;
      viewBtn.innerHTML = data.status === 'Finalized' 
        ? '<span>View Quotation &rarr;</span>' 
        : '<span>Continue Editing Draft &rarr;</span>';
    }

    const printBtn = document.getElementById('modalQuotePrintBtn');
    if (printBtn) {
      printBtn.href = data.print_url || `/sales/quotes/${quoteId}?print=1`;
    }

    const pdfBtn = document.getElementById('modalQuotePdfBtn');
    if (pdfBtn) {
      if (data.status === 'Finalized' && data.pdf_url) {
        pdfBtn.href = data.pdf_url;
        pdfBtn.setAttribute('download', `ODRA_Quotation_${data.quote_number || quoteId}.pdf`);
        pdfBtn.style.display = 'inline-flex';
      } else {
        pdfBtn.style.display = 'none';
      }
    }

    // Reveal content
    if (loadingSpinner) loadingSpinner.style.display = 'none';
    if (modalBodyContent) modalBodyContent.style.display = 'block';

  } catch (err) {
    console.error('Inspect error:', err);
    if (loadingSpinner) loadingSpinner.style.display = 'none';
    if (modalErrorBox) {
      modalErrorBox.style.display = 'block';
      modalErrorBox.innerHTML = `
        <div style="font-weight:700;margin-bottom:6px;color:#f87171;">Unable to load quotation details</div>
        <div style="color:#cbd5e1;font-size:12.5px;margin-bottom:12px;">${err.message || 'The quotation record could not be retrieved from the server.'}</div>
        <a href="/sales/quotes/${quoteId}" class="btn-primary" style="font-size:12px;padding:6px 14px;text-decoration:none;">Open Quote Builder Directly &rarr;</a>
      `;
    }
  }
};

/**
 * Fallback quick inspector
 */
window.openQuickQuoteModal = function(quoteData) {
  if (typeof quoteData === 'number' || typeof quoteData === 'string') {
    return window.inspectQuote(quoteData);
  }
  const modal = document.getElementById('quickQuoteModal');
  if (!modal) return;
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
};

window.closeQuickQuoteModal = function() {
  const modal = document.getElementById('quickQuoteModal');
  if (modal) {
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }
};
