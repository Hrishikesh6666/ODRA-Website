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

    const modeEl = document.getElementById('modalQuotePricingMode');
    if (modeEl) {
      const isBundled = (snap.enhancement_pricing_mode === 'bundled');
      modeEl.textContent = isBundled ? 'Bundled Price' : 'Itemized';
      modeEl.style.background = isBundled ? 'rgba(16,185,129,0.2)' : 'rgba(99,102,241,0.2)';
      modeEl.style.color = isBundled ? '#6ee7b7' : '#a5b4fc';
      modeEl.title = isBundled ? 'Customer sees bundled machine package price' : 'Customer sees individual itemized enhancement prices';
    }

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
      viewBtn.innerHTML = (data.status === 'Finalized' || data.can_edit === false) 
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

/**
 * Quotation Revision History Modal Handler (Async API Fetch)
 */
window.openRevisionHistory = async function(quoteId) {
  const modal = document.getElementById('revisionHistoryModal');
  if (!modal) return;

  const spinner = document.getElementById('revLoadingSpinner');
  const bodyContent = document.getElementById('revModalBodyContent');
  const errorBox = document.getElementById('revErrorBox');
  const tableBody = document.getElementById('revTableBody');
  const countBadge = document.getElementById('revTotalCount');
  const quoteBadge = document.getElementById('revModalQuoteBadge');
  const createNewBtn = document.getElementById('revCreateNewBtn');

  // Reset display
  if (spinner) spinner.style.display = 'flex';
  if (bodyContent) bodyContent.style.display = 'none';
  if (errorBox) errorBox.style.display = 'none';
  if (createNewBtn) createNewBtn.style.display = 'none';

  modal.style.display = 'flex';
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';

  try {
    const response = await fetch(`/api/quotes/${quoteId}/revisions`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      }
    });

    if (!response.ok) {
      throw new Error(`Server returned HTTP ${response.status} (${response.statusText})`);
    }

    const data = await response.json();
    const revisions = data.revisions || [];

    if (quoteBadge) quoteBadge.textContent = `Q-${data.quote_number || quoteId}`;
    if (countBadge) countBadge.textContent = `${revisions.length} total revision${revisions.length === 1 ? '' : 's'}`;

    if (tableBody) {
      tableBody.innerHTML = '';
      revisions.forEach(rev => {
        const tr = document.createElement('tr');
        tr.style.cssText = 'border-bottom: 1px solid rgba(255,255,255,0.06); transition: background 0.15s;';
        
        const isCurrent = rev.is_current;
        const isFinalized = rev.is_finalized;
        const statusBadgeClass = isFinalized 
          ? 'badge-status-finalized' 
          : 'badge-status-draft';
        const statusBadgeStyle = isFinalized
          ? 'background: #059669; color: white;'
          : 'background: #d97706; color: white;';

        tr.innerHTML = `
          <td style="padding: 12px 8px; font-weight: 700; color: white;">
            Revision ${rev.version_number}
            ${isCurrent ? '<span style="font-size: 10px; background: rgba(56, 189, 248, 0.2); color: #38bdf8; padding: 2px 6px; border-radius: 4px; margin-left: 6px; font-weight: normal;">Current</span>' : ''}
          </td>
          <td style="padding: 12px 8px; color: #94a3b8; font-size: 12px;">
            ${rev.date || 'N/A'}
          </td>
          <td style="padding: 12px 8px;">
            <span class="badge ${statusBadgeClass}" style="${statusBadgeStyle} padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">
              ${rev.status}
            </span>
          </td>
          <td style="padding: 12px 8px; color: #cbd5e1; font-size: 12.5px;">
            <strong>${rev.model || 'MT4H'}</strong>
            <span style="font-size: 11px; color: #94a3b8; margin-left: 4px;">(${rev.pricing_basis || 'MSRP'})</span>
          </td>
          <td style="padding: 12px 8px; text-align: right; font-weight: 700; color: ${isFinalized ? '#34d399' : '#fbbf24'}; font-size: 14px;">
            $${Number(rev.total || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
          </td>
          <td style="padding: 12px 8px; text-align: right;">
            <div style="display: inline-flex; gap: 6px; justify-content: flex-end; align-items: center;">
              <a href="${rev.view_url}" class="btn-rev-action btn-rev-view" style="display: inline-flex; align-items: center; justify-content: center; padding: 5px 12px; font-size: 11.5px; font-weight: 700; color: #ffffff; background: #2563eb; border: 1px solid #1d4ed8; border-radius: 5px; text-decoration: none; cursor: pointer;" title="Open Revision in Builder">
                ${(isFinalized || data.can_edit === false) ? 'View' : 'Edit'}
              </a>
              <a href="${rev.print_url}" class="btn-rev-action btn-rev-print" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; padding: 5px 12px; font-size: 11.5px; font-weight: 700; color: #0f172a; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 5px; text-decoration: none; cursor: pointer;" title="Print Revision">
                Print
              </a>
              ${isFinalized && rev.has_pdf ? `
                <a href="${rev.pdf_url}" class="btn-rev-action btn-rev-pdf" target="_blank" download style="display: inline-flex; align-items: center; justify-content: center; padding: 5px 12px; font-size: 11.5px; font-weight: 700; color: #ffffff; background: #059669; border: 1px solid #047857; border-radius: 5px; text-decoration: none; cursor: pointer;" title="Download PDF">
                  PDF
                </a>
              ` : ''}
            </div>
          </td>
        `;
        tableBody.appendChild(tr);
      });
    }

    // Configure "Create Revised Quotation" footer button if any finalized version exists and user is authorized
    if (createNewBtn && data.has_finalized && data.can_revise !== false) {
      createNewBtn.style.display = 'inline-flex';
      createNewBtn.onclick = async () => {
        if (!confirm(`Create a new draft revision for Quotation Q-${data.quote_number}?`)) return;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
          const res = await fetch(`/api/quotes/${quoteId}/revise`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            }
          });
          const resData = await res.json();
          if (!res.ok) throw new Error(resData.error || 'Failed to create revision');
          window.location.href = `/sales/quotes/${quoteId}`;
        } catch (e) {
          alert('Error: ' + e.message);
        }
      };
    }

    if (spinner) spinner.style.display = 'none';
    if (bodyContent) bodyContent.style.display = 'block';

  } catch (err) {
    console.error('Revision history error:', err);
    if (spinner) spinner.style.display = 'none';
    if (errorBox) {
      errorBox.style.display = 'block';
      errorBox.innerHTML = `
        <div style="font-weight:700;margin-bottom:6px;color:#f87171;">Unable to load revision history</div>
        <div style="color:#cbd5e1;font-size:12.5px;">${err.message || 'An error occurred while retrieving revision history.'}</div>
      `;
    }
  }
};

window.closeRevisionHistory = function() {
  const modal = document.getElementById('revisionHistoryModal');
  if (modal) {
    modal.style.display = 'none';
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }
};
