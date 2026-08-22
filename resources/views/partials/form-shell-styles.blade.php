<style>
    .form-shell {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .form-layout {
        display: grid;
        grid-template-columns: minmax(0, 22rem) minmax(0, 1fr);
        gap: 1.5rem;
    }

    .form-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .form-info-card,
    .form-main-card,
    .form-actions-card {
        border: 0;
        border-radius: 1.1rem;
        box-shadow: 0 .75rem 2rem -1.5rem rgba(15, 23, 42, 0.25);
    }

    .form-info-card {
        background: var(--form-info-bg, var(--tblr-primary-lt));
    }

    .form-info-body {
        padding: 1.5rem;
    }

    .form-info-header {
        display: flex;
        align-items: center;
        gap: .9rem;
        margin-bottom: 1.25rem;
    }

    .form-info-avatar {
        width: 3rem;
        height: 3rem;
        border-radius: .95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: var(--form-info-avatar-bg, var(--tblr-primary));
    }

    .form-info-title {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 700;
    }

    .form-info-subtitle {
        margin: .2rem 0 0;
        color: var(--tblr-secondary);
        font-size: .92rem;
    }

    .form-step-list {
        display: grid;
        gap: .6rem;
    }

    .form-step-item {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .15rem 0;
    }

    .form-step-badge {
        min-width: 1.65rem;
        height: 1.65rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .76rem;
        font-weight: 700;
        color: var(--form-info-avatar-bg, var(--tblr-primary));
        background: rgba(255, 255, 255, 0.78);
    }

    .form-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
        margin-top: 1.25rem;
    }

    .form-kpi {
        padding: .85rem .95rem;
        border-radius: .95rem;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(15, 23, 42, 0.06);
    }

    .form-kpi-label {
        display: block;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--tblr-secondary);
    }

    .form-kpi-value {
        display: block;
        margin-top: .25rem;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--tblr-body-color);
    }

    .form-main-card-header {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-start;
        justify-content: space-between;
        padding: 1.25rem 1.25rem 0;
    }

    .form-main-card-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
    }

    .form-main-card-copy {
        margin: .25rem 0 0;
        color: var(--tblr-secondary);
    }

    .form-main-card-body {
        padding: 1.25rem;
    }

    .form-summary-alert {
        padding: 1rem 1.1rem;
        border-radius: .9rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: rgba(248, 250, 252, 0.78);
    }

    .form-summary-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .form-summary-title {
        margin: 0;
        font-weight: 600;
        color: var(--tblr-dark);
    }

    .form-summary-copy {
        margin-top: .35rem;
        color: var(--tblr-secondary);
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group .form-label {
        font-weight: 600;
    }

    .form-group .form-control,
    .form-group .form-select,
    .form-group .select2-selection {
        border-radius: .9rem;
    }

    .form-help-text {
        margin-top: .45rem;
        color: var(--tblr-secondary);
        font-size: .82rem;
    }

    .form-note-list {
        display: grid;
        gap: .75rem;
    }

    .form-note-item {
        padding: .9rem 1rem;
        border-radius: .95rem;
        background: rgba(255, 255, 255, 0.74);
        border: 1px solid rgba(15, 23, 42, 0.06);
    }

    .form-note-label {
        margin-bottom: .2rem;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--tblr-secondary);
    }

    .form-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .form-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .5rem .8rem;
        border-radius: 999px;
        background: var(--tblr-bg-surface-secondary);
        color: var(--tblr-body-color);
        font-size: .83rem;
    }

    .form-preview-shell {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        text-align: center;
    }

    .form-avatar-frame,
    .form-avatar-placeholder {
        width: 9.5rem;
        height: 9.5rem;
        border-radius: 1.75rem;
        border: 3px solid rgba(14, 116, 144, 0.12);
        box-shadow: 0 1.5rem 3rem -2rem rgba(15, 23, 42, 0.55);
    }

    .form-avatar-frame {
        object-fit: cover;
    }

    .form-avatar-placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(180deg, #f8fafc, #e2e8f0);
    }

    .form-actions-card {
        padding: 1rem 1.25rem;
        background: #fff;
    }

    .form-actions-row {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        align-items: center;
        justify-content: space-between;
    }

    .form-actions-copy {
        color: var(--tblr-secondary);
    }

    .form-actions-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .invalid-feedback {
        display: block;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: calc(1.4285714em + .875rem + calc(var(--tblr-border-width) * 2));
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
    }

    .select2-container--bootstrap-5 .select2-selection.is-invalid,
    .select2-container--bootstrap-5.select2-container--focus .select2-selection.is-invalid {
        border-color: var(--tblr-danger);
        box-shadow: 0 0 0 .25rem rgba(214, 57, 57, 0.15);
    }

    @media (max-width: 991.98px) {
        .form-layout,
        .form-kpi-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
