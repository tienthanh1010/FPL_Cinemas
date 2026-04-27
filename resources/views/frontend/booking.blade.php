@extends('frontend.layout')

@section('title', 'Đặt vé | ' . $movie->title)

@php
  $ticketTypePayload = $ticketTypes->map(function ($ticketType) {
      return [
          'id' => (int) $ticketType->id,
          'code' => $ticketType->code,
          'name' => $ticketType->name,
          'description' => $ticketType->description,
      ];
  })->values()->all();

  $oldSeatIdsPayload = collect(old('seat_ids', []))->map(fn ($value) => (int) $value)->values()->all();
  $oldSeatTicketTypesPayload = collect(old('seat_ticket_types', []))->mapWithKeys(fn ($value, $seatId) => [(string) $seatId => (int) $value])->all();
<<<<<<< HEAD
  $oldProductQtyPayload = collect(old('product_qty', []))->mapWithKeys(fn ($value, $key) => [(string) $key => (int) $value])->all();
=======
  $oldProductQtyPayload = []; // F&B đã được loại bỏ khỏi luồng đặt vé
>>>>>>> origin/main
@endphp

@push('styles')
<style>
  .booking-hero {
<<<<<<< HEAD
=======

>>>>>>> origin/main
    display: grid;
    grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
    gap: 1.4rem;
    align-items: stretch;
  }
  .booking-hero__poster {
    border-radius: 26px;
<<<<<<< HEAD
=======
  .booking-hero__poster {
    border-radius: 26px;
>>>>>>> origin/main
    overflow: hidden;
    min-height: 360px;
    background: var(--surface-2);
    border: 1px solid var(--line);
    box-shadow: 0 24px 60px rgba(15, 23, 42, .12);
  }
  .booking-hero__poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .booking-hero__copy h1 {
    font-size: clamp(1.9rem, 4vw, 2.8rem);
    margin-bottom: .75rem;
  }
  .booking-hero__copy p {
    color: var(--muted);
    max-width: 760px;
  }
  .booking-meta-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .9rem;
    margin-top: 1.15rem;
  }
  .booking-meta-card {
    padding: 1rem 1.05rem;
    border-radius: 20px;
    background: var(--panel-light);
    border: 1px solid var(--line);
  }
  .booking-meta-card span {
    display: block;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--muted);
    margin-bottom: .35rem;
  }
  .booking-meta-card strong {
    color: var(--text);
    font-size: .98rem;
  }
  .booking-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.72fr) minmax(330px, .7fr);
    gap: 1.35rem;
    align-items: start;
  }
  .booking-sticky {
    position: sticky;
    top: 98px;
  }
  .booking-card {
    background: var(--panel-light);
    border: 1px solid var(--line);
    border-radius: 28px;
    padding: 1.2rem;
    box-shadow: 0 20px 42px rgba(15, 23, 42, .08);
  }
  .booking-card + .booking-card {
    margin-top: 1rem;
  }
  .booking-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
  }
  .booking-card__header h2,
  .booking-card__header h3 {
    margin: 0;
    color: var(--text);
  }
  .booking-card__header p {
    margin: .3rem 0 0;
    color: var(--muted);
  }
  .booking-live-chip,
  .booking-info-chip {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .55rem .85rem;
    border-radius: 999px;
    background: var(--surface-2);
    border: 1px solid var(--line);
    color: var(--text);
    font-size: .85rem;
    font-weight: 700;
  }
  .booking-live-chip__dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 0 6px rgba(34, 197, 94, .16);
  }
  .booking-seat-panel {
    border-radius: 26px;
    background: linear-gradient(180deg, #f7f9fc 0%, #eef3f8 100%);
    border: 1px solid rgba(15, 23, 42, .08);
    padding: 1rem;
    overflow: hidden;
  }
  .booking-seat-panel__toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
  }
  .booking-seat-panel__legend {
    display: flex;
    flex-wrap: wrap;
    gap: .9rem 1.15rem;
    align-items: center;
  }
  .seat-inline-legend {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    color: #334155;
    font-size: .9rem;
    font-weight: 700;
  }
  .seat-inline-legend__chip {
    width: 30px;
    height: 18px;
    border-radius: 999px 999px 10px 10px;
    display: inline-block;
    border: 1px solid rgba(15, 23, 42, .12);
    box-shadow: inset 0 -4px 0 rgba(15, 23, 42, .10);
  }
  .seat-board-wrap {
    border-radius: 24px;
    background: rgba(255, 255, 255, .82);
    border: 1px solid rgba(15, 23, 42, .08);
    padding: 1rem 1rem 0;
  }
  .screen-arc {
    position: relative;
    text-align: center;
    padding-top: 1.05rem;
    margin: 0 auto 1.25rem;
    width: min(880px, 95%);
  }
  .screen-arc::before {
    content: '';
    display: block;
    height: 26px;
    border-radius: 999px 999px 22px 22px;
    border: 4px solid #7c8aa3;
    border-bottom-width: 0;
    background: linear-gradient(180deg, #dce7f4 0%, #f8fbff 100%);
    box-shadow: 0 10px 18px rgba(124, 138, 163, .22);
  }
  .screen-arc span {
    display: inline-block;
    margin-top: .6rem;
    font-weight: 800;
    letter-spacing: .16em;
    color: #9aa5b5;
    text-transform: uppercase;
  }
  .seat-map-grid {
    display: grid;
    gap: .6rem;
    padding: .5rem 0 1rem;
  }
  .seat-row {
    display: grid;
    grid-template-columns: 42px minmax(0, 1fr);
    gap: .7rem;
    align-items: center;
  }
  .seat-row__label {
    width: 36px;
    height: 36px;
    display: grid;
    place-items: center;
    color: #64748b;
    font-weight: 800;
    font-size: .95rem;
  }
  .seat-row__banks {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: nowrap;
  }
  .seat-bank {
    display: flex;
    gap: .45rem;
    justify-content: center;
    flex-wrap: nowrap;
  }
  .seat-tile {
    min-width: 40px;
    height: 34px;
    border: 0;
    border-radius: 999px 999px 12px 12px;
    box-shadow: inset 0 -5px 0 rgba(15, 23, 42, .11);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 .35rem;
    transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
    color: #475569;
    position: relative;
    background: linear-gradient(180deg, #e5e7eb 0%, #c7cdd6 100%);
  }
  .seat-tile:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: inset 0 -5px 0 rgba(15, 23, 42, .13), 0 8px 14px rgba(15, 23, 42, .10);
  }
  .seat-tile:disabled {
    cursor: not-allowed;
    opacity: .96;
  }
  .seat-tile--pair {
    min-width: 82px;
  }
  .seat-tile__code {
    font-size: .76rem;
    font-weight: 900;
    line-height: 1;
  }
  .seat-tile__meta {
    display: none;
  }
  .seat-tile--AVAILABLE { background: linear-gradient(180deg, #e5e7eb 0%, #c7cdd6 100%); color: #475569; }
  .seat-tile--VIP { background: linear-gradient(180deg, #d9dde5 0%, #b8c0cc 100%); color: #475569; }
  .seat-tile--COUPLE { background: linear-gradient(180deg, #d9dde5 0%, #b8c0cc 100%); color: #475569; }
  .seat-tile--HOLD_SELF { background: linear-gradient(180deg, #7cc7ff 0%, #4ca8f1 100%); color: #fff; }
  .seat-tile--HOLD_OTHER { background: linear-gradient(180deg, #74bfff 0%, #5aa8f7 100%); color: #fff; }
  .seat-tile--RESERVED { background: linear-gradient(180deg, #ff6b57 0%, #f44336 100%); color: #fff; }
  .seat-tile--BOOKED { background: linear-gradient(180deg, #ff6b57 0%, #f44336 100%); color: #fff; }
  .seat-tile--BLOCKED { background: linear-gradient(180deg, #f7d84d 0%, #f0b90b 100%); color: #714f00; }
  .seat-board-footer {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    align-items: stretch;
    border-top: 1px solid rgba(15, 23, 42, .08);
    margin-top: 1rem;
  }
  .seat-board-footer__item {
    padding: 1rem .9rem;
    min-height: 92px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: .35rem;
  }
  .seat-board-footer__item + .seat-board-footer__item {
    border-left: 1px solid rgba(15, 23, 42, .08);
  }
  .seat-board-footer__item small {
    color: #64748b;
    font-weight: 700;
  }
  .seat-board-footer__item strong {
    color: #0f172a;
    font-size: 1rem;
  }
  .seat-board-footer__item--total strong,
  .seat-board-footer__item--countdown strong {
    font-size: clamp(1.2rem, 2vw, 2rem);
    font-weight: 900;
    color: #0f172a;
  }
  .seat-board-footer__seat {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    color: #334155;
    font-weight: 700;
  }
  .seat-board-footer__status {
    margin-top: .35rem;
    color: #64748b;
    line-height: 1.55;
    font-size: .88rem;
  }
  .seat-board-footer__status strong { font-size: .95rem; }
  .seat-legend-card {
    border-radius: 22px;
    border: 1px solid var(--line);
    background: var(--surface-2);
    padding: 1rem;
  }
  .seat-legend-card h3 {
    font-size: 1rem;
    margin-bottom: .8rem;
    color: var(--text);
  }
  .seat-legend-list {
    display: grid;
    gap: .7rem;
  }
  .seat-legend-item {
    display: flex;
    gap: .75rem;
    align-items: flex-start;
    color: var(--text);
  }
  .seat-legend-swatch {
    width: 22px;
    height: 22px;
    border-radius: 8px;
    flex: 0 0 auto;
    border: 1px solid rgba(15, 23, 42, .08);
    box-shadow: inset 0 -5px 0 rgba(15, 23, 42, .08);
  }
  .swatch-regular { background: linear-gradient(180deg, #e5e7eb 0%, #c7cdd6 100%); }
  .swatch-vip { background: linear-gradient(180deg, #d9dde5 0%, #b8c0cc 100%); }
  .swatch-couple { background: linear-gradient(180deg, #d9dde5 0%, #b8c0cc 100%); }
  .swatch-hold-self { background: linear-gradient(180deg, #7cc7ff 0%, #4ca8f1 100%); }
  .swatch-hold-other { background: linear-gradient(180deg, #74bfff 0%, #5aa8f7 100%); }
  .swatch-reserved { background: linear-gradient(180deg, #ff6b57 0%, #f44336 100%); }
  .swatch-booked { background: linear-gradient(180deg, #ff6b57 0%, #f44336 100%); }
  .swatch-blocked { background: linear-gradient(180deg, #f7d84d 0%, #f0b90b 100%); }
  .seat-legend-item strong {
    display: block;
    font-size: .9rem;
    margin-bottom: .1rem;
  }
  .seat-legend-item span {
    color: var(--muted);
    font-size: .82rem;
    line-height: 1.45;
  }
  .seat-selection-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: .9rem;
  }
  .seat-selection-card {
    border-radius: 22px;
    border: 1px solid var(--line);
    background: var(--surface-2);
    padding: .95rem;
  }
  .seat-selection-card__head {
    display: flex;
    justify-content: space-between;
    gap: .75rem;
    align-items: start;
    margin-bottom: .75rem;
  }
  .seat-selection-card__head strong {
    display: block;
    color: var(--text);
  }
  .seat-selection-card__head span {
    color: var(--muted);
    font-size: .82rem;
  }
  .seat-selection-card__price {
    font-size: .82rem;
    color: var(--muted);
    margin-top: .5rem;
  }
  .ticket-type-pill-list {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    margin-top: .9rem;
  }
  .ticket-type-pill {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .75rem;
    border-radius: 999px;
    background: var(--surface-2);
    border: 1px solid var(--line);
    color: var(--text);
    font-size: .82rem;
    font-weight: 700;
  }
  .ticket-type-pill small {
    color: var(--muted);
    font-weight: 500;
  }
  .booking-note-box {
    border-radius: 22px;
    border: 1px dashed color-mix(in srgb, var(--primary) 38%, var(--line));
    background: color-mix(in srgb, var(--primary) 8%, var(--panel-light));
    padding: .95rem 1rem;
    color: var(--text);
  }
  .booking-note-box p {
    margin: 0;
    color: var(--muted);
  }
  .booking-status-box {
    border-radius: 20px;
    border: 1px solid var(--line);
    background: var(--surface-2);
    padding: .95rem 1rem;
    color: var(--text);
    font-size: .9rem;
  }
  .booking-status-box strong { color: var(--text); }
  .product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
  }
  .product-card {
    border-radius: 24px;
    border: 1px solid var(--line);
    background: var(--surface-2);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 100%;
  }
  .product-card.is-disabled { opacity: .55; }
  .product-card__media {
    aspect-ratio: 16 / 10;
    background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 14%, #fff), var(--surface-2));
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    font-size: .95rem;
    overflow: hidden;
  }
  .product-card__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .product-card__body {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: .85rem;
    height: 100%;
  }
  .product-card__badges {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }
  .product-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .34rem .7rem;
    border-radius: 999px;
    background: var(--panel-light);
    border: 1px solid var(--line);
    color: var(--text);
    font-size: .74rem;
    font-weight: 700;
  }
  .product-badge--combo {
    background: color-mix(in srgb, var(--primary) 14%, var(--panel-light));
  }
  .product-card__title {
    font-size: 1rem;
    font-weight: 800;
    color: var(--text);
  }
  .product-card__description {
    color: var(--muted);
    font-size: .9rem;
    min-height: 44px;
  }
  .product-card__footer {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: .9rem;
    margin-top: auto;
  }
  .product-price {
    font-size: 1.05rem;
    font-weight: 900;
    color: var(--text);
  }
  .product-stock {
    color: var(--muted);
    font-size: .82rem;
  }
  .product-qty-control {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    border-radius: 999px;
    padding: .35rem;
    border: 1px solid var(--line);
    background: var(--panel-light);
  }
  .product-qty-button {
    width: 34px;
    height: 34px;
    border: 0;
    border-radius: 50%;
    background: var(--surface-2);
    color: var(--text);
    font-weight: 800;
  }
  .product-qty-button:disabled {
    opacity: .45;
    cursor: not-allowed;
  }
  .product-qty-input {
    width: 52px;
    border: 0;
    background: transparent;
    color: var(--text);
    text-align: center;
    font-weight: 800;
    outline: none;
  }
  .summary-breakdown {
    display: grid;
    gap: .7rem;
  }
  .summary-breakdown__row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    color: var(--muted);
    font-size: .92rem;
  }
  .summary-breakdown__row strong { color: var(--text); }
  .summary-separator {
    border-top: 1px solid var(--line);
    margin: .2rem 0;
  }
  .summary-total {
    font-size: 1.9rem;
    font-weight: 900;
    line-height: 1.1;
    color: var(--text);
  }
  .selected-seat-pills {
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
  }
  .selected-seat-pill {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .48rem .78rem;
    border-radius: 999px;
    background: var(--surface-2);
    border: 1px solid var(--line);
    color: var(--text);
    font-size: .84rem;
    font-weight: 700;
  }
  .form-field label {
    display: block;
    margin-bottom: .45rem;
    font-weight: 700;
    color: var(--text);
  }
  .booking-submit-note {
    margin-top: .95rem;
    color: var(--muted);
    font-size: .88rem;
    line-height: 1.65;
  }
  .booking-empty {
    border-radius: 20px;
    border: 1px dashed var(--line);
    padding: 1rem;
    color: var(--muted);
    text-align: center;
  }
  .booking-alert-inline {
    display: none;
    margin-bottom: .9rem;
    padding: .85rem 1rem;
    border-radius: 18px;
    font-size: .9rem;
    border: 1px solid transparent;
  }
  .booking-alert-inline.is-visible { display: block; }
  .booking-alert-inline[data-level="error"] {
    background: rgba(239, 68, 68, .08);
    border-color: rgba(239, 68, 68, .22);
    color: #dc2626;
  }
  .booking-alert-inline[data-level="info"] {
    background: rgba(59, 130, 246, .08);
    border-color: rgba(59, 130, 246, .22);
    color: #2563eb;
  }
  .booking-alert-inline[data-level="success"] {
    background: rgba(34, 197, 94, .08);
    border-color: rgba(34, 197, 94, .22);
    color: #16a34a;
  }
  @media (max-width: 1199.98px) {
    .booking-layout {
      grid-template-columns: 1fr;
    }
    .booking-sticky {
      position: static;
    }
    .seat-board-footer {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .seat-board-footer__item:nth-child(odd) {
      border-left: 0;
    }
  }
  @media (max-width: 991.98px) {
    .booking-hero {
      grid-template-columns: 1fr;
    }
    .booking-hero__poster {
      max-width: 300px;
      min-height: 320px;
    }
    .booking-meta-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .booking-seat-panel__toolbar {
      align-items: flex-start;
    }
    .seat-row__banks {
      gap: 1rem;
    }
  }
  @media (max-width: 767.98px) {
    .booking-meta-grid {
      grid-template-columns: 1fr;
    }
    .seat-row {
      grid-template-columns: 1fr;
      justify-items: center;
    }
    .seat-row__banks {
      gap: .8rem;
      width: 100%;
      overflow-x: auto;
      justify-content: flex-start;
      padding-bottom: .25rem;
    }
    .seat-board-footer {
      grid-template-columns: 1fr;
    }
    .seat-board-footer__item + .seat-board-footer__item {
      border-left: 0;
      border-top: 1px solid rgba(15, 23, 42, .08);
    }
  }
<<<<<<< HEAD
=======
    overflow: hidden;
    min-height: 360px;
    background: var(--surface-2);
    border: 1px solid var(--line);
    box-shadow: 0 24px 60px rgba(15, 23, 42, .12);
  }
  .booking-hero__poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .booking-hero__copy h1 {
    font-size: clamp(1.9rem, 4vw, 2.8rem);
    margin-bottom: .75rem;
  }
  .booking-hero__copy p {
    color: var(--muted);
    max-width: 760px;
  }
  .booking-meta-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .9rem;
    margin-top: 1.15rem;
  }
  .booking-meta-card {
    padding: 1rem 1.05rem;
    border-radius: 20px;
    background: var(--panel-light);
    border: 1px solid var(--line);
  }
  .booking-meta-card span {
    display: block;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--muted);
    margin-bottom: .35rem;
  }
  .booking-meta-card strong {
    color: var(--text);
    font-size: .98rem;
  }
  .booking-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.72fr) minmax(330px, .7fr);
    gap: 1.35rem;
    align-items: start;
  }
  .booking-sticky {
    position: sticky;
    top: 98px;
  }
  .booking-card {
    background: var(--panel-light);
    border: 1px solid var(--line);
    border-radius: 24px;
    padding: 1rem;
    box-shadow: 0 16px 32px rgba(15, 23, 42, .08);
  }
  .booking-card + .booking-card {
    margin-top: 1rem;
  }
  .booking-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: .85rem;
    margin-bottom: .85rem;
    flex-wrap: wrap;
  }
  .booking-card__header h2,
  .booking-card__header h3 {
    margin: 0;
    color: var(--text);
  }
  .booking-card__header p {
    margin: .3rem 0 0;
    color: var(--muted);
  }
  .booking-live-chip,
  .booking-info-chip {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .48rem .78rem;
    border-radius: 999px;
    background: var(--surface-2);
    border: 1px solid var(--line);
    color: var(--text);
    font-size: .8rem;
    font-weight: 700;
  }
  .booking-live-chip__dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 0 6px rgba(34, 197, 94, .16);
  }
  .booking-seat-panel {
    border-radius: 22px;
    background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
    border: 1px solid rgba(15, 23, 42, .08);
    padding: .85rem;
    overflow: hidden;
  }
  .booking-seat-panel__toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    align-items: center;
    justify-content: flex-start;
    margin-bottom: .8rem;
  }
  .booking-seat-panel__legend {
    display: flex;
    flex-wrap: wrap;
    gap: .55rem .9rem;
    align-items: center;
  }
  .seat-inline-legend {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    color: #334155;
    font-size: .82rem;
    font-weight: 700;
  }
  .seat-inline-legend__chip {
    width: 26px;
    height: 16px;
    border-radius: 999px 999px 10px 10px;
    display: inline-block;
    border: 1px solid rgba(15, 23, 42, .12);
    box-shadow: inset 0 -4px 0 rgba(15, 23, 42, .10);
  }
  .seat-board-wrap {
    border-radius: 20px;
    background: rgba(255, 255, 255, .88);
    border: 1px solid rgba(15, 23, 42, .08);
    padding: .8rem .85rem 0;
  }
  .screen-arc {
    position: relative;
    text-align: center;
    padding-top: .8rem;
    margin: 0 auto 1rem;
    width: min(860px, 95%);
  }
  .screen-arc::before {
    content: '';
    display: block;
    height: 20px;
    border-radius: 999px 999px 22px 22px;
    border: 4px solid #7c8aa3;
    border-bottom-width: 0;
    background: linear-gradient(180deg, #dce7f4 0%, #f8fbff 100%);
    box-shadow: 0 10px 18px rgba(124, 138, 163, .22);
  }
  .screen-arc span {
    display: inline-block;
    margin-top: .45rem;
    font-weight: 800;
    letter-spacing: .14em;
    color: #9aa5b5;
    text-transform: uppercase;
    font-size: .95rem;
  }
  .seat-map-grid {
    display: grid;
    gap: .45rem;
    padding: .25rem 0 .85rem;
  }
  .seat-row {
    display: grid;
    grid-template-columns: 34px minmax(0, 1fr);
    gap: .55rem;
    align-items: center;
  }
  .seat-row__label {
    width: 30px;
    height: 30px;
    display: grid;
    place-items: center;
    color: #64748b;
    font-weight: 800;
    font-size: .95rem;
  }
  .seat-row__banks {
    display: flex;
    justify-content: center;
    gap: 1.3rem;
    flex-wrap: nowrap;
  }
  .seat-bank {
    display: flex;
    gap: .35rem;
    justify-content: center;
    flex-wrap: nowrap;
  }
  .seat-tile {
    min-width: 38px;
    height: 32px;
    border: 0;
    border-radius: 999px 999px 12px 12px;
    box-shadow: inset 0 -5px 0 rgba(15, 23, 42, .11);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 .35rem;
    transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
    color: #475569;
    position: relative;
    background: linear-gradient(180deg, #e5e7eb 0%, #c7cdd6 100%);
  }
  .seat-tile:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: inset 0 -5px 0 rgba(15, 23, 42, .13), 0 8px 14px rgba(15, 23, 42, .10);
  }
  .seat-tile:disabled {
    cursor: not-allowed;
    opacity: .96;
  }
  .seat-tile--pair {
    min-width: 78px;
  }
  .seat-tile__code {
    font-size: .72rem;
    font-weight: 900;
    line-height: 1;
  }
  .seat-tile__meta {
    display: none;
  }
  .seat-tile--AVAILABLE { background: linear-gradient(180deg, #e5e7eb 0%, #c7cdd6 100%); color: #475569; }
  .seat-tile--VIP { background: linear-gradient(180deg, #d9dde5 0%, #b8c0cc 100%); color: #475569; }
  .seat-tile--COUPLE { background: linear-gradient(180deg, #d9dde5 0%, #b8c0cc 100%); color: #475569; }
  .seat-tile--HOLD_SELF { background: linear-gradient(180deg, #7cc7ff 0%, #4ca8f1 100%); color: #fff; }
  .seat-tile--HOLD_OTHER { background: linear-gradient(180deg, #74bfff 0%, #5aa8f7 100%); color: #fff; }
  .seat-tile--RESERVED { background: linear-gradient(180deg, #ff6b57 0%, #f44336 100%); color: #fff; }
  .seat-tile--BOOKED { background: linear-gradient(180deg, #ff6b57 0%, #f44336 100%); color: #fff; }
  .seat-tile--BLOCKED { background: linear-gradient(180deg, #f7d84d 0%, #f0b90b 100%); color: #714f00; }
  .seat-board-footer {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr 1fr;
    align-items: stretch;
    border-top: 1px solid rgba(15, 23, 42, .08);
    margin-top: .7rem;
  }
  .seat-board-footer__item {
    padding: .85rem .8rem;
    min-height: 78px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: .35rem;
  }
  .seat-board-footer__item + .seat-board-footer__item {
    border-left: 1px solid rgba(15, 23, 42, .08);
  }
  .seat-board-footer__item small {
    color: #64748b;
    font-weight: 700;
    font-size: .78rem;
  }
  .seat-board-footer__item strong {
    color: #0f172a;
    font-size: .98rem;
  }
  .seat-board-footer__item--total strong,
  .seat-board-footer__item--countdown strong {
    font-size: clamp(1.1rem, 1.6vw, 1.8rem);
    font-weight: 900;
    color: #0f172a;
  }
  .seat-board-footer__seat {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    color: #334155;
    font-weight: 700;
  }
  .seat-board-footer__status {
    margin-top: .2rem;
    color: #64748b;
    line-height: 1.45;
    font-size: .82rem;
  }
  .seat-type-inline-list {
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
    align-items: center;
  }
  .seat-type-inline-list .seat-inline-legend {
    padding: .22rem .48rem;
    background: rgba(15, 23, 42, .04);
    border-radius: 999px;
  }
  .seat-board-footer__status strong { font-size: .95rem; }
  .seat-legend-card {
    border-radius: 22px;
    border: 1px solid var(--line);
    background: var(--surface-2);
    padding: 1rem;
  }
  .seat-legend-card h3 {
    font-size: 1rem;
    margin-bottom: .8rem;
    color: var(--text);
  }
  .seat-legend-list {
    display: grid;
    gap: .7rem;
  }
  .seat-legend-item {
    display: flex;
    gap: .75rem;
    align-items: flex-start;
    color: var(--text);
  }
  .seat-legend-swatch {
    width: 22px;
    height: 22px;
    border-radius: 8px;
    flex: 0 0 auto;
    border: 1px solid rgba(15, 23, 42, .08);
    box-shadow: inset 0 -5px 0 rgba(15, 23, 42, .08);
  }
  .swatch-regular { background: linear-gradient(180deg, #e5e7eb 0%, #c7cdd6 100%); }
  .swatch-vip { background: linear-gradient(180deg, #d9dde5 0%, #b8c0cc 100%); }
  .swatch-couple { background: linear-gradient(180deg, #d9dde5 0%, #b8c0cc 100%); }
  .swatch-hold-self { background: linear-gradient(180deg, #7cc7ff 0%, #4ca8f1 100%); }
  .swatch-hold-other { background: linear-gradient(180deg, #74bfff 0%, #5aa8f7 100%); }
  .swatch-reserved { background: linear-gradient(180deg, #ff6b57 0%, #f44336 100%); }
  .swatch-booked { background: linear-gradient(180deg, #ff6b57 0%, #f44336 100%); }
  .swatch-blocked { background: linear-gradient(180deg, #f7d84d 0%, #f0b90b 100%); }
  .seat-legend-item strong {
    display: block;
    font-size: .9rem;
    margin-bottom: .1rem;
  }
  .seat-legend-item span {
    color: var(--muted);
    font-size: .82rem;
    line-height: 1.45;
  }
  .seat-selection-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: .9rem;
  }
  .seat-selection-card {
    border-radius: 22px;
    border: 1px solid var(--line);
    background: var(--surface-2);
    padding: .95rem;
  }
  .seat-selection-card__head {
    display: flex;
    justify-content: space-between;
    gap: .75rem;
    align-items: start;
    margin-bottom: .75rem;
  }
  .seat-selection-card__head strong {
    display: block;
    color: var(--text);
  }
  .seat-selection-card__head span {
    color: var(--muted);
    font-size: .82rem;
  }
  .seat-selection-card__price {
    font-size: .82rem;
    color: var(--muted);
    margin-top: .5rem;
  }
  .ticket-type-pill-list {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    margin-top: .9rem;
  }
  .ticket-type-pill {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .75rem;
    border-radius: 999px;
    background: var(--surface-2);
    border: 1px solid var(--line);
    color: var(--text);
    font-size: .82rem;
    font-weight: 700;
  }
  .ticket-type-pill small {
    color: var(--muted);
    font-weight: 500;
  }
  .booking-note-box {
    border-radius: 22px;
    border: 1px dashed color-mix(in srgb, var(--primary) 38%, var(--line));
    background: color-mix(in srgb, var(--primary) 8%, var(--panel-light));
    padding: .95rem 1rem;
    color: var(--text);
  }
  .booking-note-box p {
    margin: 0;
    color: var(--muted);
  }
  .booking-status-box {
    border-radius: 20px;
    border: 1px solid var(--line);
    background: var(--surface-2);
    padding: .95rem 1rem;
    color: var(--text);
    font-size: .9rem;
  }
  .booking-status-box strong { color: var(--text); }
  .product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
  }
  .product-card {
    border-radius: 24px;
    border: 1px solid var(--line);
    background: var(--surface-2);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 100%;
  }
  .product-card.is-disabled { opacity: .55; }
  .product-card__media {
    aspect-ratio: 16 / 10;
    background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 14%, #fff), var(--surface-2));
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    font-size: .95rem;
    overflow: hidden;
  }
  .product-card__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .product-card__body {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: .85rem;
    height: 100%;
  }
  .product-card__badges {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }
  .product-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .34rem .7rem;
    border-radius: 999px;
    background: var(--panel-light);
    border: 1px solid var(--line);
    color: var(--text);
    font-size: .74rem;
    font-weight: 700;
  }
  .product-badge--combo {
    background: color-mix(in srgb, var(--primary) 14%, var(--panel-light));
  }
  .product-card__title {
    font-size: 1rem;
    font-weight: 800;
    color: var(--text);
  }
  .product-card__description {
    color: var(--muted);
    font-size: .9rem;
    min-height: 44px;
  }
  .product-card__footer {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: .9rem;
    margin-top: auto;
  }
  .product-price {
    font-size: 1.05rem;
    font-weight: 900;
    color: var(--text);
  }
  .product-stock {
    color: var(--muted);
    font-size: .82rem;
  }
  .product-qty-control {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    border-radius: 999px;
    padding: .35rem;
    border: 1px solid var(--line);
    background: var(--panel-light);D
  }
  .product-qty-button {
    width: 34px;
    height: 34px;
    border: 0;
    border-radius: 50%;
    background: var(--surface-2);
    color: var(--text);
    font-weight: 800;
  }
  .product-qty-button:disabled {
    opacity: .45;
    cursor: not-allowed;
  }
  .product-qty-input {
    width: 52px;
    border: 0;
    background: transparent;
    color: var(--text);
    text-align: center;
    font-weight: 800;
    outline: none;
  }
  .summary-breakdown {
    display: grid;
    gap: .7rem;
  }
  .summary-breakdown__row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    color: var(--muted);
    font-size: .92rem;
  }
  .summary-breakdown__row strong { color: var(--text); }
  .summary-separator {
    border-top: 1px solid var(--line);
    margin: .2rem 0;
  }
  .summary-total {
    font-size: 1.9rem;
    font-weight: 900;
    line-height: 1.1;
    color: var(--text);
  }
  .selected-seat-pills {
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
  }
  .selected-seat-pill {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .48rem .78rem;
    border-radius: 999px;
    background: var(--surface-2);
    border: 1px solid var(--line);
    color: var(--text);
    font-size: .84rem;
    font-weight: 700;
  }
  .form-field label {
    display: block;
    margin-bottom: .45rem;
    font-weight: 700;
    color: var(--text);
  }
  .booking-submit-note {
    margin-top: .95rem;
    color: var(--muted);
    font-size: .88rem;
    line-height: 1.65;
  }
  .booking-empty {
    border-radius: 20px;
    border: 1px dashed var(--line);
    padding: 1rem;
    color: var(--muted);
    text-align: center;
  }
  .booking-alert-inline {
    display: none;
    margin-bottom: .9rem;
    padding: .9rem 1rem;
    border-radius: 18px;
    border: 1px solid transparent;
    box-shadow: 0 14px 34px rgba(15, 23, 42, .10);
  }
  .booking-alert-inline.is-visible {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    gap: .85rem;
    align-items: start;
  }
  .booking-alert-inline__icon {
    width: 36px;
    height: 36px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1rem;
    box-shadow: inset 0 -3px 0 rgba(15, 23, 42, .08);
  }
  .booking-alert-inline__body strong {
    display: block;
    margin-bottom: .2rem;
    font-size: .95rem;
  }
  .booking-alert-inline__body span {
    display: block;
    line-height: 1.55;
    font-size: .9rem;
  }
  .booking-alert-inline__close {
    border: 0;
    background: transparent;
    color: inherit;
    font-size: 1.1rem;
    line-height: 1;
    padding: .15rem;
    opacity: .72;
  }
  .booking-alert-inline__close:hover { opacity: 1; }
  .booking-alert-inline.is-visible[data-level="error"] {
    background: rgba(239, 68, 68, .10);
    border-color: rgba(239, 68, 68, .26);
    color: #dc2626;
  }
  .booking-alert-inline.is-visible[data-level="error"] .booking-alert-inline__icon {
    background: rgba(239, 68, 68, .14);
  }
  .booking-alert-inline.is-visible[data-level="info"] {
    background: rgba(59, 130, 246, .10);
    border-color: rgba(59, 130, 246, .24);
    color: #2563eb;
  }
  .booking-alert-inline.is-visible[data-level="info"] .booking-alert-inline__icon {
    background: rgba(59, 130, 246, .14);
  }
  .booking-alert-inline.is-visible[data-level="success"] {
    background: rgba(34, 197, 94, .10);
    border-color: rgba(34, 197, 94, .24);
    color: #16a34a;
  }
  .booking-alert-inline.is-visible[data-level="success"] .booking-alert-inline__icon {
    background: rgba(34, 197, 94, .14);
  }
  @media (max-width: 1199.98px) {
    .booking-layout {
      grid-template-columns: 1fr;
    }
    .booking-sticky {
      position: static;
    }
    .seat-board-footer {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .seat-board-footer__item:nth-child(odd) {
      border-left: 0;
    }
  }
  @media (max-width: 991.98px) {
    .booking-hero {
      grid-template-columns: 1fr;
    }
    .booking-hero__poster {
      max-width: 300px;
      min-height: 320px;
    }
    .booking-meta-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .booking-seat-panel__toolbar {
      align-items: flex-start;
    }
    .seat-row__banks {
      gap: 1rem;
    }
  }
  @media (max-width: 767.98px) {
    .booking-meta-grid {
      grid-template-columns: 1fr;
    }
    .seat-row {
      grid-template-columns: 1fr;
      justify-items: center;
    }
    .seat-row__banks {
      gap: .8rem;
      width: 100%;
      overflow-x: auto;
      justify-content: flex-start;
      padding-bottom: .25rem;
    }
    .seat-board-footer {
      grid-template-columns: 1fr;
    }
    .seat-board-footer__item + .seat-board-footer__item {
      border-left: 0;
      border-top: 1px solid rgba(15, 23, 42, .08);
    }
  }
>>>>>>> origin/main
</style>
@endpush

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel mb-4">
        <div class="booking-hero">
          <div class="booking-hero__poster">
<<<<<<< HEAD
=======
        <div class="booking-hero">
          <div class="booking-hero__poster">
>>>>>>> origin/main
            @if($movie->poster_url)
              <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
            @else
              <div class="poster-fallback poster-fallback--showtime h-100"><span>{{ $movie->title }}</span></div>
            @endif
          </div>
          <div class="booking-hero__copy">
            <span class="section-eyebrow">Trang đặt ghế riêng</span>
            <h1>{{ $movie->title }}</h1>
            <p>
              Suất chiếu bạn chọn: <strong class="text-white">{{ $show->start_time->translatedFormat('l, d/m/Y H:i') }}</strong>.
              Trang này đã được tách riêng để bạn chọn ghế trên sơ đồ lớn, theo dõi trạng thái ghế theo thời gian thực,
              gán loại vé cho từng ghế, thêm combo bắp nước và chuyển sang bước thanh toán QR.
            </p>

<<<<<<< HEAD
=======
            @if($movie->poster_url)
              <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
            @else
              <div class="poster-fallback poster-fallback--showtime h-100"><span>{{ $movie->title }}</span></div>
            @endif
          </div>
          <div class="booking-hero__copy">
            <span class="section-eyebrow">Trang đặt ghế riêng</span>
            <h1>{{ $movie->title }}</h1>
            <p>
              Suất chiếu bạn chọn: <strong class="text-white">{{ $show->start_time->translatedFormat('l, d/m/Y H:i') }}</strong>.
              Trang này đã được tách riêng để bạn chọn ghế trên sơ đồ lớn, theo dõi trạng thái ghế theo thời gian thực,
              gán loại vé cho từng ghế, thêm combo bắp nước và chuyển sang bước thanh toán QR.
            </p>


>>>>>>> origin/main
            <div class="hero-meta hero-meta--compact">
              <span><i class="bi bi-clock me-2"></i>{{ $movie->duration_minutes }} phút</span>
              <span><i class="bi bi-camera-reels me-2"></i>{{ $show->movieVersion?->format ?: '2D' }}</span>
              <span><i class="bi bi-door-open me-2"></i>{{ $show->auditorium?->name ?: 'Phòng chiếu' }}</span>
              <span><i class="bi bi-person-badge me-2"></i>{{ $movie->contentRating?->code ?: 'P' }}</span>
<<<<<<< HEAD
=======
              <span><i class="bi bi-person-badge me-2"></i>{{ $movie->contentRating?->code ?: 'P' }}</span>
>>>>>>> origin/main
            </div>

            <div class="booking-meta-grid">
              <div class="booking-meta-card">
                <span>Rạp chiếu</span>
                <strong>{{ $show->auditorium?->cinema?->name ?: 'FPL Cinema' }}</strong>
              </div>
              <div class="booking-meta-card">
                <span>Khung giờ</span>
                <strong>{{ $show->start_time->format('H:i') }} → {{ optional($show->end_time)->format('H:i') }}</strong>
              </div>
              <div class="booking-meta-card">
                <span>Trạng thái</span>
                <strong>{{ $show->frontendStatusLabel() }}</strong>
              </div>
              <div class="booking-meta-card">
                <span>Giữ ghế</span>
                <strong>{{ $bookingConfig['hold_minutes'] }} phút / lần giữ</strong>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
              <a href="{{ route('movies.showtimes', $movie) }}" class="btn btn-cinema-secondary"><i class="bi bi-arrow-left me-2"></i>Quay lại lịch chiếu</a>
              @if($relatedShows->isNotEmpty())
                <span class="booking-info-chip"><i class="bi bi-calendar2-week"></i>Còn {{ $relatedShows->count() }} suất khác của phim này</span>
              @endif
              @if($bookingConfig['child_ticket_blocked'])
                <span class="booking-info-chip"><i class="bi bi-shield-exclamation"></i>Phim {{ $movie->contentRating?->code }} không cho chọn vé trẻ em</span>
<<<<<<< HEAD
=======
            </div>

            <div class="booking-meta-grid">
              <div class="booking-meta-card">
                <span>Rạp chiếu</span>
                <strong>{{ $show->auditorium?->cinema?->name ?: 'FPL Cinema' }}</strong>
              </div>
              <div class="booking-meta-card">
                <span>Khung giờ</span>
                <strong>{{ $show->start_time->format('H:i') }} → {{ optional($show->end_time)->format('H:i') }}</strong>
              </div>
              <div class="booking-meta-card">
                <span>Trạng thái</span>
                <strong>{{ $show->frontendStatusLabel() }}</strong>
              </div>
              <div class="booking-meta-card">
                <span>Giữ ghế</span>
                <strong>{{ $bookingConfig['hold_minutes'] }} phút / lần giữ</strong>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
              <a href="{{ route('movies.showtimes', $movie) }}" class="btn btn-cinema-secondary"><i class="bi bi-arrow-left me-2"></i>Quay lại lịch chiếu</a>
              @if($relatedShows->isNotEmpty())
                <span class="booking-info-chip"><i class="bi bi-calendar2-week"></i>Còn {{ $relatedShows->count() }} suất khác của phim này</span>
              @endif
              @if($bookingConfig['child_ticket_blocked'])
                <span class="booking-info-chip"><i class="bi bi-shield-exclamation"></i>Phim {{ $movie->contentRating?->code }} không cho chọn vé trẻ em</span>

>>>>>>> origin/main
              @endif
            </div>
          </div>
        </div>
      </div>

      <form method="POST" action="{{ route('booking.store') }}" id="bookingForm">
        @csrf
        <input type="hidden" name="show_id" value="{{ $show->id }}">
        <input type="hidden" name="qty" id="qtyInput" value="{{ old('qty', 0) }}">
        <div id="seatInputs"></div>

        <div class="booking-layout">
<<<<<<< HEAD
=======
        <div class="booking-layout">

>>>>>>> origin/main
          <div>
            @if($errors->any())
              <div class="app-alert app-alert--error mb-4">
                <div class="fw-semibold mb-2">Không thể tạo booking, vui lòng kiểm tra lại:</div>
                <ul class="mb-0 ps-3">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="booking-alert-inline" id="bookingInlineAlert" data-level="info"></div>

            <div class="booking-card">
              <div class="booking-card__header">
                <div>
<<<<<<< HEAD
                  <h2>Sơ đồ ghế trực quan</h2>
                  <p>
                    Giao diện chọn ghế đã được làm lại theo dạng bản đồ lớn giống form rạp chiếu phim,
                    giúp bạn nhìn rõ trạng thái ghế ngay khi kéo trang và thao tác nhanh hơn.
                  </p>
                </div>
                <div class="booking-live-chip" id="liveSeatStatus">
                  <span class="booking-live-chip__dot"></span>
                  <span>Đang đồng bộ ghế realtime</span>
                </div>
              </div>

              <div class="booking-note-box mb-3">
                <p>
                  Quy tắc hợp lệ: ghế đôi phải chọn theo cặp liền nhau, hệ thống không cho để lại 1 ghế lẻ trong cùng dãy,
                  và mỗi ghế có thể gán một loại vé riêng như 2 vé người lớn + 1 vé trẻ em nếu phim cho phép.
                </p>
              </div>
=======
                  <h2>Chọn ghế</h2>
                  <p>Chọn nhanh ghế phù hợp trên sơ đồ gọn hơn, rõ hơn và dễ thao tác hơn.</p>
                </div>
                <div class="booking-live-chip" id="liveSeatStatus">
                  <span class="booking-live-chip__dot"></span>
                  <span>Đồng bộ ghế realtime</span>
                </div>
              </div>

>>>>>>> origin/main

              <div class="booking-seat-panel">
                <div class="booking-seat-panel__toolbar">
                  <div class="booking-seat-panel__legend">
                    <span class="seat-inline-legend"><span class="seat-inline-legend__chip swatch-regular"></span>Ghế trống</span>
                    <span class="seat-inline-legend"><span class="seat-inline-legend__chip swatch-hold-self"></span>Ghế đang chọn</span>
                    <span class="seat-inline-legend"><span class="seat-inline-legend__chip swatch-hold-other"></span>Ghế đang giữ</span>
                    <span class="seat-inline-legend"><span class="seat-inline-legend__chip swatch-booked"></span>Ghế đã bán</span>
                    <span class="seat-inline-legend"><span class="seat-inline-legend__chip swatch-blocked"></span>Ghế đặt trước</span>
                  </div>
<<<<<<< HEAD
                  <div class="booking-info-chip">
                    <i class="bi bi-arrows-fullscreen"></i>Khung ghế đã được mở rộng
                  </div>
=======
>>>>>>> origin/main
                </div>

                <div class="seat-board-wrap">
                  <div class="screen-arc"><span>Màn hình chiếu</span></div>
                  <div id="seatMap" class="seat-map-grid"></div>

                  <div class="seat-board-footer">
                    <div class="seat-board-footer__item">
<<<<<<< HEAD
                      <small>Loại ghế</small>
                      <div class="seat-board-footer__seat"><span class="seat-inline-legend__chip swatch-regular"></span>Ghế thường</div>
                    </div>
                    <div class="seat-board-footer__item">
                      <small>Loại ghế</small>
                      <div class="seat-board-footer__seat"><span class="seat-inline-legend__chip swatch-couple"></span>Ghế đôi</div>
=======
                      <small>Phân khu ghế</small>
                      <div class="seat-type-inline-list">
                        <span class="seat-inline-legend"><span class="seat-inline-legend__chip swatch-regular"></span>Thường</span>
                        <span class="seat-inline-legend"><span class="seat-inline-legend__chip swatch-vip"></span>VIP</span>
                        <span class="seat-inline-legend"><span class="seat-inline-legend__chip swatch-couple"></span>Đôi</span>
                      </div>
>>>>>>> origin/main
                    </div>
                    <div class="seat-board-footer__item">
                      <small>Phiên giữ ghế</small>
                      <div class="seat-board-footer__status" id="holdStatusBox">Bạn chưa chọn ghế nào.</div>
                    </div>
                    <div class="seat-board-footer__item seat-board-footer__item--total">
                      <small>Tổng tiền</small>
                      <strong id="seatBoardTotalValue">0đ</strong>
                    </div>
                    <div class="seat-board-footer__item seat-board-footer__item--countdown">
                      <small>Thời gian còn lại</small>
                      <strong id="holdCountdownValue">00:00</strong>
                    </div>
                  </div>
                </div>
<<<<<<< HEAD
=======

>>>>>>> origin/main
              </div>
            </div>

            <div class="booking-card">
              <div class="booking-card__header">
                <div>
                  <h3>Ghế đã chọn & loại vé theo từng ghế</h3>
                  <p>Ví dụ: bạn có thể chọn 2 vé người lớn và 1 vé trẻ em trong cùng một booking.</p>
                </div>
                <div class="booking-info-chip" id="selectedSeatCount">0 ghế</div>
<<<<<<< HEAD
=======
          <div class="booking-sticky">
            <div class="booking-card">
              <h3 class="mb-3">Thông tin người đặt</h3>
              <div class="form-field mb-3">
                <label>Họ và tên</label>
                <input class="form-control cinema-input" name="contact_name" value="{{ old('contact_name', $authCustomer?->full_name ?: auth()->user()?->name) }}" placeholder="Nguyễn Văn A" required>
>>>>>>> origin/main
              </div>

              <div class="selected-seat-pills mb-3" id="selectedSeatPills"></div>
              <div id="selectedSeatAssignments" class="seat-selection-grid"></div>
              <div id="selectedSeatEmpty" class="booking-empty">Chưa có ghế nào được chọn.</div>
            </div>
<<<<<<< HEAD

            <div class="booking-card">
              <div class="booking-card__header">
                <div>
                  <h3>Combo bắp nước & đồ ăn kèm</h3>
                  <p>Phần combo đã được tách riêng với vé xem phim. Bạn có thể thêm ảnh và mô tả cho từng món trong admin.</p>
                </div>
                <div class="booking-info-chip" id="selectedProductCount">0 món</div>
              </div>
              <div id="productCatalog" class="product-grid"></div>
            </div>
=======
>>>>>>> origin/main
          </div>

          <div class="booking-sticky">
            <div class="booking-card">
              <h3 class="mb-3">Thông tin người đặt</h3>
              <div class="form-field mb-3">
                <label>Họ và tên</label>
                <input class="form-control cinema-input" name="contact_name" value="{{ old('contact_name', $authCustomer?->full_name ?: auth()->user()?->name) }}" placeholder="Nguyễn Văn A" required>
<<<<<<< HEAD
=======


>>>>>>> origin/main
              </div>
              <div class="form-field mb-3">
                <label>Điện thoại</label>
                <input class="form-control cinema-input" name="contact_phone" value="{{ old('contact_phone', $authCustomer?->phone) }}" placeholder="0900 000 000" required>
              </div>
              <div class="form-field mb-3">
                <label>Email</label>
                <input class="form-control cinema-input" type="email" name="contact_email" value="{{ old('contact_email', $authCustomer?->email ?: auth()->user()?->email) }}" placeholder="name@example.com">
              </div>
              <div class="form-field mb-0">
                <label>Mã giảm giá / voucher</label>
                <input class="form-control cinema-input text-uppercase" name="coupon_code" id="couponInput" value="{{ old('coupon_code') }}" placeholder="Ví dụ: CINEMA20">
              </div>
            </div>

            <div class="booking-card">
              <h3 class="mb-3">Loại vé đang hỗ trợ</h3>
              <div class="ticket-type-pill-list">
                @foreach($ticketTypes as $ticketType)
                  <span class="ticket-type-pill">
                    {{ $ticketType->name }}
                    @if($ticketType->description)
                      <small>{{ $ticketType->description }}</small>
                    @endif
                  </span>
                @endforeach
              </div>
              @if($bookingConfig['child_ticket_blocked'])
                <div class="booking-note-box mt-3">
                  <p>Phim này thuộc diện hạn chế độ tuổi, hệ thống đã tự ẩn lựa chọn vé trẻ em ở bước gán loại vé.</p>
                </div>
              @endif
            </div>

            <div class="booking-card">
              <div class="booking-card__header">
                <div>
                  <h3>Tóm tắt đơn hàng</h3>
<<<<<<< HEAD
                  <p>Tổng tiền sẽ tự tính theo loại ghế, loại vé và combo bạn chọn.</p>
=======
                  <p>Tổng tiền sẽ tự tính theo loại ghế và loại vé bạn chọn.</p>
>>>>>>> origin/main
                </div>
              </div>
              <div class="summary-breakdown" id="summaryBreakdown"></div>
              <div class="summary-separator"></div>
              <div class="booking-note">Tạm tính toàn bộ đơn hàng</div>
              <div class="summary-total" id="bookingTotalValue">0đ</div>
              <div class="booking-submit-note" id="loyaltyPreview"></div>
            </div>

            <button class="btn btn-cinema-primary w-100 mt-3" type="submit" id="bookingSubmitButton">
<<<<<<< HEAD
              <i class="bi bi-ticket-detailed me-2"></i>Tạo booking và sang bước thanh toán
=======
              <i class="bi bi-ticket-detailed me-2"></i>Xác nhận ghế và sang bước thanh toán
>>>>>>> origin/main
            </button>
            <p class="booking-submit-note mb-0">
              Sau khi xác nhận, ghế sẽ được chuyển sang bước thanh toán và chỉ giữ tối đa {{ $bookingConfig['hold_minutes'] }} phút.
              Nếu quá thời gian mà chưa thanh toán, booking sẽ tự động hết hạn và ghế được mở lại cho khách khác.
            </p>
<<<<<<< HEAD
=======

>>>>>>> origin/main
          </div>
        </div>
      </form>
    </div>
  </section>
@endsection

@push('scripts')
<script>
(() => {
  const bookingConfig = @json($bookingConfig);
  const seatStatusUrl = @json(route('shows.seats.status', $show));
  const seatSyncUrl = @json(route('shows.seats.sync', $show));
  const ticketTypes = @json($ticketTypePayload);
<<<<<<< HEAD
=======
  const seatStatusUrl = @json(route('shows.seats.status', $show));
  const seatSyncUrl = @json(route('shows.seats.sync', $show));
  const ticketTypes = @json($ticketTypePayload);
  const oldState = {
    seatIds: @json($oldSeatIdsPayload),
    seatTicketTypes: @json($oldSeatTicketTypesPayload),
    productQty: @json($oldProductQtyPayload),
  };

  const form = document.getElementById('bookingForm');
  const seatMap = document.getElementById('seatMap');
  const seatInputs = document.getElementById('seatInputs');
  const qtyInput = document.getElementById('qtyInput');
  const selectedSeatCount = document.getElementById('selectedSeatCount');
  const selectedSeatPills = document.getElementById('selectedSeatPills');
  const selectedSeatAssignments = document.getElementById('selectedSeatAssignments');
  const selectedSeatEmpty = document.getElementById('selectedSeatEmpty');
>>>>>>> origin/main
  const oldState = {
    seatIds: @json($oldSeatIdsPayload),
    seatTicketTypes: @json($oldSeatTicketTypesPayload),
    productQty: @json($oldProductQtyPayload),
  };

  const form = document.getElementById('bookingForm');
  const seatMap = document.getElementById('seatMap');
  const seatInputs = document.getElementById('seatInputs');
  const qtyInput = document.getElementById('qtyInput');
  const selectedSeatCount = document.getElementById('selectedSeatCount');
  const selectedSeatPills = document.getElementById('selectedSeatPills');
  const selectedSeatAssignments = document.getElementById('selectedSeatAssignments');
  const selectedSeatEmpty = document.getElementById('selectedSeatEmpty');
  const productCatalog = document.getElementById('productCatalog');
  const selectedProductCount = document.getElementById('selectedProductCount');
<<<<<<< HEAD
=======

>>>>>>> origin/main
  const summaryBreakdown = document.getElementById('summaryBreakdown');
  const bookingTotalValue = document.getElementById('bookingTotalValue');
  const loyaltyPreview = document.getElementById('loyaltyPreview');
  const bookingSubmitButton = document.getElementById('bookingSubmitButton');
  const liveSeatStatus = document.getElementById('liveSeatStatus');
  const holdStatusBox = document.getElementById('holdStatusBox');
  const holdCountdownValue = document.getElementById('holdCountdownValue');
  const seatBoardTotalValue = document.getElementById('seatBoardTotalValue');
  const inlineAlert = document.getElementById('bookingInlineAlert');

  if (!form || !seatMap) {
    return;
  }

  const csrfToken = form.querySelector('input[name="_token"]')?.value || '';
  const isMember = @json(auth()->check());
  const amountPerPoint = Number(@json((int) config('loyalty.amount_per_point', 10000)));
  const maxSeats = Number(bookingConfig.max_seats_per_booking || 10);
  const holdMinutes = Number(bookingConfig.hold_minutes || 2);
  const seatPollSeconds = Number(bookingConfig.seat_poll_seconds || 5);
  const pairSeatCodes = ['COUPLE', 'SWEETBOX'];
  const defaultTicketTypeId = Number(ticketTypes[0]?.id || 0);

  const state = {
    seats: Array.isArray(bookingConfig.seats) ? bookingConfig.seats : [],
    selectedSeatIds: [],
    seatTicketTypes: Object.fromEntries(Object.entries(oldState.seatTicketTypes || {}).map(([seatId, ticketTypeId]) => [String(seatId), Number(ticketTypeId)])),
<<<<<<< HEAD
    selectedProductQty: Object.fromEntries(Object.entries(oldState.productQty || {}).map(([productId, qty]) => [String(productId), Number(qty || 0)])),
=======
>>>>>>> origin/main
    syncTimer: null,
    pollTimer: null,
    isSyncing: false,
    lastAlertKey: null,
    holdDeadlineAt: null,
    holdCountdownTimer: null,
<<<<<<< HEAD
=======
    serverTimeOffsetMs: 0,
    alertTimer: null,
>>>>>>> origin/main
  };

  const initialSeatIds = new Set([
    ...((oldState.seatIds || []).map(Number)),
    ...state.seats.filter((seat) => seat.selected_by_self).map((seat) => Number(seat.id)),
  ]);
  state.selectedSeatIds = Array.from(initialSeatIds).filter(Boolean);

  const formatCurrency = (value) => `${Number(value || 0).toLocaleString('vi-VN')}đ`;
  const ticketTypeMap = Object.fromEntries(ticketTypes.map((ticketType) => [String(ticketType.id), ticketType]));
<<<<<<< HEAD
  const productMap = Object.fromEntries((bookingConfig.products || []).map((product) => [String(product.id), product]));
  const showAlert = (message, level = 'info', key = null) => {
    if (key && state.lastAlertKey === key) {
      return;
    }
    state.lastAlertKey = key || null;
    inlineAlert.textContent = message;
    inlineAlert.dataset.level = level;
    inlineAlert.classList.add('is-visible');
=======
  const productMap = {};
  const showAlert = (message, level = 'info', key = null, title = null) => {
    if (key && state.lastAlertKey === key && inlineAlert.classList.contains('is-visible')) {
      return;
    }

    if (state.alertTimer) {
      window.clearTimeout(state.alertTimer);
      state.alertTimer = null;
    }

    state.lastAlertKey = key || null;
    const iconMap = { error: '!', info: 'i', success: '✓' };
    const titleMap = { error: 'Chưa thể chọn ghế này', info: 'Thông báo', success: 'Đã cập nhật' };
    inlineAlert.dataset.level = level;
    inlineAlert.innerHTML = `
      <div class="booking-alert-inline__icon">${iconMap[level] || 'i'}</div>
      <div class="booking-alert-inline__body">
        <strong>${title || titleMap[level] || 'Thông báo'}</strong>
        <span>${message}</span>
      </div>
      <button type="button" class="booking-alert-inline__close" aria-label="Đóng">×</button>
    `;
    inlineAlert.classList.add('is-visible');
    inlineAlert.querySelector('.booking-alert-inline__close')?.addEventListener('click', () => clearAlert(key));

    if (level !== 'error') {
      state.alertTimer = window.setTimeout(() => clearAlert(key), 4500);
    }
>>>>>>> origin/main
  };

  const clearAlert = (key = null) => {
    if (key && state.lastAlertKey && state.lastAlertKey !== key) {
      return;
    }
<<<<<<< HEAD
    state.lastAlertKey = null;
    inlineAlert.classList.remove('is-visible');
    inlineAlert.textContent = '';
=======
    if (state.alertTimer) {
      window.clearTimeout(state.alertTimer);
      state.alertTimer = null;
    }
    state.lastAlertKey = null;
    inlineAlert.classList.remove('is-visible');
    inlineAlert.innerHTML = '';
>>>>>>> origin/main
  };

  const seatMapById = () => Object.fromEntries(state.seats.map((seat) => [String(seat.id), seat]));
  const getSeatById = (seatId) => seatMapById()[String(seatId)] || null;

  const getSeatPrice = (seat, ticketTypeId) => {
    const matrix = bookingConfig.prices?.[seat.seat_type_id] || bookingConfig.prices?.[String(seat.seat_type_id)] || {};
    return Number(matrix[String(ticketTypeId)] ?? matrix[ticketTypeId] ?? 0);
  };

  const normalizeTicketTypeSelections = () => {
    const allowedTicketTypeIds = new Set(ticketTypes.map((ticketType) => Number(ticketType.id)));
    state.selectedSeatIds.forEach((seatId) => {
      const key = String(seatId);
      const current = Number(state.seatTicketTypes[key] || 0);
      if (!allowedTicketTypeIds.has(current)) {
        state.seatTicketTypes[key] = defaultTicketTypeId;
      }
    });

    Object.keys(state.seatTicketTypes).forEach((seatId) => {
      if (!state.selectedSeatIds.includes(Number(seatId))) {
        delete state.seatTicketTypes[seatId];
      }
    });
  };

  const syncHiddenInputs = () => {
    normalizeTicketTypeSelections();
    qtyInput.value = String(state.selectedSeatIds.length);
    seatInputs.innerHTML = state.selectedSeatIds.map((seatId) => {
      const ticketTypeId = Number(state.seatTicketTypes[String(seatId)] || defaultTicketTypeId || 0);
      return `
        <input type="hidden" name="seat_ids[]" value="${seatId}">
        <input type="hidden" name="seat_ticket_types[${seatId}]" value="${ticketTypeId}">
      `;
    }).join('');

    bookingSubmitButton.disabled = state.selectedSeatIds.length === 0 || state.isSyncing;
  };

  const buildSeatTileClass = (seat) => {
    if (seat.state === 'HOLD_SELF') return 'HOLD_SELF';
    if (seat.state === 'HOLD_OTHER') return 'HOLD_OTHER';
    if (seat.state === 'RESERVED') return 'RESERVED';
    if (seat.state === 'BOOKED') return 'BOOKED';
    if (seat.state === 'BLOCKED') return 'BLOCKED';
    if (pairSeatCodes.includes(String(seat.seat_type_code).toUpperCase())) return 'COUPLE';
    if (String(seat.seat_type_code).toUpperCase() === 'VIP') return 'VIP';
    return 'AVAILABLE';
  };
<<<<<<< HEAD
=======
  const chunkArray = (items, size) => {
    const chunkSize = Math.max(1, Number(size) || 1);
    const chunks = [];
    for (let index = 0; index < items.length; index += chunkSize) {
      chunks.push(items.slice(index, index + chunkSize));
    }
    return chunks;
  };

>>>>>>> origin/main
  const rowGroups = () => Object.entries(
    state.seats.reduce((rows, seat) => {
      rows[String(seat.row_label)] = rows[String(seat.row_label)] || [];
      rows[String(seat.row_label)].push(seat);
      return rows;
    }, {})
  )
    .sort(([rowA], [rowB]) => String(rowA).localeCompare(String(rowB), undefined, { numeric: true }))
    .map(([rowLabel, seats]) => ({
      rowLabel,
      seats: seats.sort((left, right) => Number(left.col_number) - Number(right.col_number)),
    }));
<<<<<<< HEAD
=======
  };

  const buildProductCard = (product, qty) => {
    const safeQty = Math.max(0, Number(qty || 0));
    const maxQty = Math.min(20, Number(product.qty_on_hand || 0));
    const imageHtml = product.image_url
      ? `<img src="${product.image_url}" alt="${product.name}">`
      : `<span>${product.is_combo ? 'Combo bắp nước' : 'F&B tại quầy'}</span>`;

    return `
      <div class="product-card ${product.available ? '' : 'is-disabled'}">
        <div class="product-card__media">${imageHtml}</div>
        <div class="product-card__body">
          <div class="product-card__badges">
            <span class="product-badge ${product.is_combo ? 'product-badge--combo' : ''}">${product.is_combo ? 'Combo' : product.category}</span>
            <span class="product-badge">${product.unit || 'ITEM'}</span>
          </div>
          <div>
            <div class="product-card__title">${product.name}</div>
            <div class="product-card__description">${product.description || 'Sản phẩm được phục vụ tại quầy F&B của rạp.'}</div>
          </div>
          <div class="product-card__footer">
            <div>
              <div class="product-price">${formatCurrency(product.price_amount)}</div>
              <div class="product-stock">${product.available ? `Còn ${product.qty_on_hand} ${product.unit || 'món'}` : 'Tạm hết hàng'}</div>
            </div>
            <div class="product-qty-control">
              <button type="button" class="product-qty-button" data-product-action="decrease" data-product-id="${product.id}" ${product.available ? '' : 'disabled'}>−</button>
              <input class="product-qty-input" type="number" min="0" max="${maxQty}" step="1" name="product_qty[${product.id}]" value="${safeQty}" data-product-input data-product-id="${product.id}" ${product.available ? '' : 'disabled'}>
              <button type="button" class="product-qty-button" data-product-action="increase" data-product-id="${product.id}" ${product.available ? '' : 'disabled'}>+</button>
            </div>
>>>>>>> origin/main

  const renderSeatMap = () => {
    if (!state.seats.length) {
      seatMap.innerHTML = '<div class="booking-empty">Suất chiếu này chưa có sơ đồ ghế khả dụng.</div>';
      return;
    }

    const html = rowGroups().map(({ rowLabel, seats }) => {
<<<<<<< HEAD
      const midpoint = Math.ceil(seats.length / 2);
      const banks = [seats.slice(0, midpoint), seats.slice(midpoint)].filter((bank) => bank.length);
      const bankHtml = banks.map((bank) => `
        <div class="seat-bank">
=======
      const isPairRow = seats.length > 0 && seats.every((seat) => pairSeatCodes.includes(String(seat.seat_type_code).toUpperCase()));
      const banks = isPairRow
        ? chunkArray(seats, 3)
        : [seats.slice(0, Math.ceil(seats.length / 2)), seats.slice(Math.ceil(seats.length / 2))].filter((bank) => bank.length);

      const bankHtml = banks.map((bank) => `
        <div class="seat-bank ${isPairRow ? 'seat-bank--pair' : ''}">
>>>>>>> origin/main
          ${bank.map((seat) => {
            const seatClass = buildSeatTileClass(seat);
            return `
              <button type="button"
                      class="seat-tile seat-tile--${seatClass} ${pairSeatCodes.includes(String(seat.seat_type_code).toUpperCase()) ? 'seat-tile--pair' : ''}"
                      data-seat-id="${seat.id}"
                      title="${seat.seat_code} · ${seat.seat_type_name} · ${seat.state_label}"
                      ${seat.available ? '' : 'disabled'}>
                <span class="seat-tile__code">${seat.seat_code}</span>
                <span class="seat-tile__meta">${seat.seat_type_name}</span>
                <span class="seat-tile__meta">${seat.state_label}</span>
              </button>
            `;
          }).join('')}
        </div>
      `).join('');

      return `
<<<<<<< HEAD
        <div class="seat-row">
          <div class="seat-row__label">${rowLabel}</div>
          <div class="seat-row__banks">${bankHtml}</div>
=======
        <div class="seat-row ${isPairRow ? 'seat-row--pair' : ''}">
          <div class="seat-row__label">${rowLabel}</div>
          <div class="seat-row__banks ${isPairRow ? 'seat-row__banks--pair' : ''}">${bankHtml}</div>
>>>>>>> origin/main
        </div>
      `;
    }).join('');

    seatMap.innerHTML = html;

    seatMap.querySelectorAll('[data-seat-id]').forEach((button) => {
      button.addEventListener('click', () => toggleSeatSelection(Number(button.dataset.seatId || 0)));
    });
  };

<<<<<<< HEAD
=======
  const applyServerClock = (serverTimeIso) => {
    if (!serverTimeIso) return;
    const serverMillis = new Date(serverTimeIso).getTime();
    if (!Number.isNaN(serverMillis)) {
      state.serverTimeOffsetMs = serverMillis - Date.now();
    }
  };

  const applyHoldExpiry = (expiresAtIso) => {
    if (!expiresAtIso) {
      state.holdDeadlineAt = null;
      return;
    }
    const expiresMillis = new Date(expiresAtIso).getTime();
    state.holdDeadlineAt = Number.isNaN(expiresMillis) ? null : expiresMillis;
  };

  const nowFromServerClock = () => Date.now() + Number(state.serverTimeOffsetMs || 0);

  const humanizeRowList = (rows) => rows.length === 1 ? `dãy ${rows[0]}` : `các dãy ${rows.join(', ')}`;

>>>>>>> origin/main
  const formatCountdown = (totalSeconds) => {
    const safeSeconds = Math.max(0, Number(totalSeconds || 0));
    const minutes = Math.floor(safeSeconds / 60).toString().padStart(2, '0');
    const seconds = Math.floor(safeSeconds % 60).toString().padStart(2, '0');
    return `${minutes}:${seconds}`;
  };

  const stopHoldCountdown = () => {
    if (state.holdCountdownTimer) {
      window.clearInterval(state.holdCountdownTimer);
      state.holdCountdownTimer = null;
    }
<<<<<<< HEAD
    state.holdDeadlineAt = null;
=======
>>>>>>> origin/main
    if (holdCountdownValue) {
      holdCountdownValue.textContent = '00:00';
    }
  };

  const startHoldCountdown = () => {
<<<<<<< HEAD
    if (!state.selectedSeatIds.length) {
=======
    if (!state.holdDeadlineAt) {
>>>>>>> origin/main
      stopHoldCountdown();
      return;
    }

<<<<<<< HEAD
    state.holdDeadlineAt = Date.now() + (holdMinutes * 60 * 1000);
    if (state.holdCountdownTimer) {
      window.clearInterval(state.holdCountdownTimer);
    }

    const tick = () => {
      const secondsLeft = Math.max(0, Math.ceil((state.holdDeadlineAt - Date.now()) / 1000));
=======
    if (state.holdCountdownTimer) {
      window.clearInterval(state.holdCountdownTimer);
    }
  };

    const tick = () => {
      const secondsLeft = Math.max(0, Math.ceil((state.holdDeadlineAt - nowFromServerClock()) / 1000));
>>>>>>> origin/main
      if (holdCountdownValue) {
        holdCountdownValue.textContent = formatCountdown(secondsLeft);
      }
      if (secondsLeft <= 0) {
        window.clearInterval(state.holdCountdownTimer);
        state.holdCountdownTimer = null;
<<<<<<< HEAD
=======
        state.holdDeadlineAt = null;
>>>>>>> origin/main
        setLiveMessage('Phiên giữ ghế đã hết hạn, đang làm mới trạng thái...');
        fetchSeatStatus();
      }
    };

    tick();
    state.holdCountdownTimer = window.setInterval(tick, 1000);
  };

  const renderSelectedSeatAssignments = () => {
    const selectedSeats = state.selectedSeatIds
      .map((seatId) => getSeatById(seatId))
      .filter(Boolean)
      .sort((left, right) => {
        if (left.row_label === right.row_label) {
          return Number(left.col_number) - Number(right.col_number);
        }
        return String(left.row_label).localeCompare(String(right.row_label), undefined, { numeric: true });
      });

    selectedSeatCount.textContent = `${selectedSeats.length} ghế`;
    selectedSeatPills.innerHTML = selectedSeats.length
      ? selectedSeats.map((seat) => `<span class="selected-seat-pill">${seat.seat_code} · ${seat.seat_type_name}</span>`).join('')
      : '<span class="booking-empty w-100">Chưa có ghế nào được chọn.</span>';

    selectedSeatEmpty.style.display = selectedSeats.length ? 'none' : 'block';
    selectedSeatAssignments.innerHTML = selectedSeats.map((seat) => {
      const seatId = Number(seat.id);
      const selectedTicketTypeId = Number(state.seatTicketTypes[String(seatId)] || defaultTicketTypeId || 0);
      const options = ticketTypes.map((ticketType) => `
        <option value="${ticketType.id}" ${selectedTicketTypeId === Number(ticketType.id) ? 'selected' : ''}>
          ${ticketType.name}
        </option>
      `).join('');
      const price = getSeatPrice(seat, selectedTicketTypeId);

      return `
        <div class="seat-selection-card">
          <div class="seat-selection-card__head">
            <div>
              <strong>${seat.seat_code}</strong>
              <span>${seat.seat_type_name} · ${seat.state_label}</span>
            </div>
            <button type="button" class="btn btn-sm btn-cinema-secondary" data-remove-seat="${seat.id}">Bỏ</button>
<<<<<<< HEAD
=======

>>>>>>> origin/main
          </div>
          <label class="form-label small text-muted mb-2">Loại vé cho ghế này</label>
          <select class="form-select cinema-select" data-seat-ticket-type="${seat.id}">${options}</select>
          <div class="seat-selection-card__price">Giá ghế hiện tại: <strong>${formatCurrency(price)}</strong></div>
        </div>
      `;
    }).join('');

    selectedSeatAssignments.querySelectorAll('[data-seat-ticket-type]').forEach((select) => {
      select.addEventListener('change', () => {
        const seatId = String(select.dataset.seatTicketType || '');
        state.seatTicketTypes[seatId] = Number(select.value || defaultTicketTypeId || 0);
        renderSummary();
        syncHiddenInputs();
      });
    });

    selectedSeatAssignments.querySelectorAll('[data-remove-seat]').forEach((button) => {
      button.addEventListener('click', () => toggleSeatSelection(Number(button.dataset.removeSeat || 0)));
    });
  };

<<<<<<< HEAD
  const buildProductCard = (product, qty) => {
    const safeQty = Math.max(0, Number(qty || 0));
    const maxQty = Math.min(20, Number(product.qty_on_hand || 0));
    const imageHtml = product.image_url
      ? `<img src="${product.image_url}" alt="${product.name}">`
      : `<span>${product.is_combo ? 'Combo bắp nước' : 'F&B tại quầy'}</span>`;

    return `
      <div class="product-card ${product.available ? '' : 'is-disabled'}">
        <div class="product-card__media">${imageHtml}</div>
        <div class="product-card__body">
          <div class="product-card__badges">
            <span class="product-badge ${product.is_combo ? 'product-badge--combo' : ''}">${product.is_combo ? 'Combo' : product.category}</span>
            <span class="product-badge">${product.unit || 'ITEM'}</span>
          </div>
          <div>
            <div class="product-card__title">${product.name}</div>
            <div class="product-card__description">${product.description || 'Sản phẩm được phục vụ tại quầy F&B của rạp.'}</div>
          </div>
          <div class="product-card__footer">
            <div>
              <div class="product-price">${formatCurrency(product.price_amount)}</div>
              <div class="product-stock">${product.available ? `Còn ${product.qty_on_hand} ${product.unit || 'món'}` : 'Tạm hết hàng'}</div>
            </div>
            <div class="product-qty-control">
              <button type="button" class="product-qty-button" data-product-action="decrease" data-product-id="${product.id}" ${product.available ? '' : 'disabled'}>−</button>
              <input class="product-qty-input" type="number" min="0" max="${maxQty}" step="1" name="product_qty[${product.id}]" value="${safeQty}" data-product-input data-product-id="${product.id}" ${product.available ? '' : 'disabled'}>
              <button type="button" class="product-qty-button" data-product-action="increase" data-product-id="${product.id}" ${product.available ? '' : 'disabled'}>+</button>
            </div>
          </div>
        </div>
      </div>
    `;
  };

  const sanitizeSelectedProducts = () => {
    state.selectedProductQty = Object.fromEntries(
      Object.entries(state.selectedProductQty)
        .map(([productId, qty]) => {
          const product = productMap[String(productId)];
          if (!product || !product.available) {
            return null;
          }
          const maxQty = Math.min(20, Number(product.qty_on_hand || 0));
          return [String(productId), Math.max(0, Math.min(maxQty, Number(qty || 0)))];
        })
        .filter(Boolean)
    );
  };

  const renderProducts = () => {
    const products = Array.isArray(bookingConfig.products) ? bookingConfig.products : [];
    sanitizeSelectedProducts();

    if (!products.length) {
      productCatalog.innerHTML = '<div class="booking-empty">Hiện chưa có sản phẩm F&B hoạt động cho rạp này.</div>';
      selectedProductCount.textContent = '0 món';
      return;
    }

    productCatalog.innerHTML = products.map((product) => buildProductCard(product, state.selectedProductQty[String(product.id)] || 0)).join('');
    const totalProducts = Object.values(state.selectedProductQty).reduce((sum, qty) => sum + Number(qty || 0), 0);
    selectedProductCount.textContent = `${totalProducts} món`;

    productCatalog.querySelectorAll('[data-product-action]').forEach((button) => {
      button.addEventListener('click', () => {
        const productId = String(button.dataset.productId || '');
        const product = productMap[productId];
        if (!product) return;
        const currentQty = Number(state.selectedProductQty[productId] || 0);
        const maxQty = Math.min(20, Number(product.qty_on_hand || 0));
        state.selectedProductQty[productId] = button.dataset.productAction === 'increase'
          ? Math.min(maxQty, currentQty + 1)
          : Math.max(0, currentQty - 1);
        renderProducts();
        renderSummary();
      });
    });

    productCatalog.querySelectorAll('[data-product-input]').forEach((input) => {
      input.addEventListener('input', () => {
        const productId = String(input.dataset.productId || '');
        const product = productMap[productId];
        if (!product) return;
        const maxQty = Math.min(20, Number(product.qty_on_hand || 0));
        state.selectedProductQty[productId] = Math.max(0, Math.min(maxQty, Number(input.value || 0)));
        renderProducts();
        renderSummary();
      });
    });
  };
=======
  const renderProducts = () => {};
>>>>>>> origin/main

  const findPairSeat = (seat) => {
    if (!seat || !pairSeatCodes.includes(String(seat.seat_type_code).toUpperCase())) {
      return null;
    }
    const sameRowSeats = state.seats
      .filter((item) => String(item.row_label) === String(seat.row_label) && String(item.seat_type_code).toUpperCase() === String(seat.seat_type_code).toUpperCase())
      .sort((left, right) => Number(left.col_number) - Number(right.col_number));
    const seatIndex = sameRowSeats.findIndex((item) => Number(item.id) === Number(seat.id));
    if (seatIndex === -1) {
      return null;
    }
    if (seatIndex % 2 === 0) {
      return sameRowSeats[seatIndex + 1] || null;
    }
    return sameRowSeats[seatIndex - 1] || null;
  };

  const findSingleGapRows = (candidateSeatIds) => {
    const candidateMap = Object.fromEntries(candidateSeatIds.map((seatId) => [String(seatId), true]));
    const busyStates = new Set(['HOLD_OTHER', 'RESERVED', 'BOOKED', 'BLOCKED']);
    const rows = {};

    state.seats.forEach((seat) => {
      rows[seat.row_label] = rows[seat.row_label] || [];
      rows[seat.row_label].push(seat);
    });

    const invalidRows = [];
    Object.entries(rows).forEach(([rowLabel, seats]) => {
      const sorted = seats.sort((left, right) => Number(left.col_number) - Number(right.col_number));
      let currentSegment = [];
      let previousCol = null;
      const segments = [];

      sorted.forEach((seat) => {
        if (previousCol !== null && (Number(seat.col_number) - Number(previousCol)) > 1) {
          segments.push(currentSegment);
          currentSegment = [];
        }
        currentSegment.push(seat);
        previousCol = Number(seat.col_number);
      });

      if (currentSegment.length) {
        segments.push(currentSegment);
      }

      segments.forEach((segment) => {
        let availableRun = 0;
        segment.forEach((seat) => {
          const isUnavailable = candidateMap[String(seat.id)] || busyStates.has(String(seat.state));
          if (isUnavailable) {
            if (availableRun === 1 && !invalidRows.includes(rowLabel)) {
              invalidRows.push(rowLabel);
            }
            availableRun = 0;
            return;
          }
          availableRun += 1;
        });

        if (availableRun === 1 && !invalidRows.includes(rowLabel)) {
          invalidRows.push(rowLabel);
        }
      });
    });

    return invalidRows;
  };

  const setLiveMessage = (message) => {
    liveSeatStatus.querySelector('span:last-child').textContent = message;
  };

  const updateHoldBox = () => {
<<<<<<< HEAD
    if (!state.selectedSeatIds.length) {
      holdStatusBox.innerHTML = 'Bạn chưa chọn ghế nào.';
      stopHoldCountdown();
      return;
    }
    holdStatusBox.innerHTML = `Bạn đang giữ tạm <strong>${state.selectedSeatIds.length} ghế</strong>. Sau <strong>${holdMinutes} phút</strong> không thanh toán, ghế sẽ tự nhả cho khách khác.`;
=======
    if (!state.holdDeadlineAt) {
      holdStatusBox.innerHTML = 'Bộ đếm sẽ bắt đầu ngay khi bạn chọn ghế đầu tiên.';
      stopHoldCountdown();
      return;
    }

    if (!state.selectedSeatIds.length) {
      holdStatusBox.innerHTML = 'Phiên giữ ghế vẫn đang chạy. Bạn có thể chọn lại ghế khác mà đồng hồ sẽ không bị đặt lại từ đầu.';
      startHoldCountdown();
      return;
    }

    holdStatusBox.innerHTML = `Bạn đang giữ tạm <strong>${state.selectedSeatIds.length} ghế</strong>. Đồng hồ sẽ tiếp tục chạy đến khi bạn thanh toán hoặc hết thời gian giữ ghế.`;
  const sanitizeSelectedProducts = () => {
    state.selectedProductQty = Object.fromEntries(
      Object.entries(state.selectedProductQty)
        .map(([productId, qty]) => {
          const product = productMap[String(productId)];
          if (!product || !product.available) {
            return null;
          }
          const maxQty = Math.min(20, Number(product.qty_on_hand || 0));
          return [String(productId), Math.max(0, Math.min(maxQty, Number(qty || 0)))];
        })
        .filter(Boolean)
    );
  };

  const renderProducts = () => {
    const products = Array.isArray(bookingConfig.products) ? bookingConfig.products : [];
    sanitizeSelectedProducts();

    if (!products.length) {
      productCatalog.innerHTML = '<div class="booking-empty">Hiện chưa có sản phẩm F&B hoạt động cho rạp này.</div>';
      selectedProductCount.textContent = '0 món';
      return;
    }

    productCatalog.innerHTML = products.map((product) => buildProductCard(product, state.selectedProductQty[String(product.id)] || 0)).join('');
    const totalProducts = Object.values(state.selectedProductQty).reduce((sum, qty) => sum + Number(qty || 0), 0);
    selectedProductCount.textContent = `${totalProducts} món`;

    productCatalog.querySelectorAll('[data-product-action]').forEach((button) => {
      button.addEventListener('click', () => {
        const productId = String(button.dataset.productId || '');
        const product = productMap[productId];
        if (!product) return;
        const currentQty = Number(state.selectedProductQty[productId] || 0);
        const maxQty = Math.min(20, Number(product.qty_on_hand || 0));
        state.selectedProductQty[productId] = button.dataset.productAction === 'increase'
>>>>>>> origin/main
  };

  const renderSummary = () => {
    const selectedSeats = state.selectedSeatIds.map((seatId) => getSeatById(seatId)).filter(Boolean);
    const ticketSubtotal = selectedSeats.reduce((sum, seat) => {
      const ticketTypeId = Number(state.seatTicketTypes[String(seat.id)] || defaultTicketTypeId || 0);
      return sum + getSeatPrice(seat, ticketTypeId);
    }, 0);

<<<<<<< HEAD
    const productSubtotal = Object.entries(state.selectedProductQty).reduce((sum, [productId, qty]) => {
      const product = productMap[String(productId)];
      if (!product || !product.available) return sum;
      return sum + (Number(qty || 0) * Number(product.price_amount || 0));
    }, 0);

    const total = ticketSubtotal + productSubtotal;
=======
    const total = ticketSubtotal;
>>>>>>> origin/main
    const selectedTicketBreakdown = selectedSeats.map((seat) => {
      const ticketType = ticketTypeMap[String(state.seatTicketTypes[String(seat.id)] || defaultTicketTypeId || 0)];
      return `${seat.seat_code} (${ticketType?.name || 'Loại vé'})`;
    }).join(', ');

    summaryBreakdown.innerHTML = `
      <div class="summary-breakdown__row"><span>Suất chiếu</span><strong>${bookingConfig.show_date}</strong></div>
      <div class="summary-breakdown__row"><span>Khung giờ</span><strong>${bookingConfig.start_time} → ${bookingConfig.end_time}</strong></div>
      <div class="summary-breakdown__row"><span>Ghế / loại vé</span><strong>${selectedTicketBreakdown || 'Chưa chọn'}</strong></div>
      <div class="summary-breakdown__row"><span>Tiền vé</span><strong>${formatCurrency(ticketSubtotal)}</strong></div>
<<<<<<< HEAD
      <div class="summary-breakdown__row"><span>Combo / F&B</span><strong>${formatCurrency(productSubtotal)}</strong></div>
=======
>>>>>>> origin/main
    `;
    bookingTotalValue.textContent = formatCurrency(total);
    if (seatBoardTotalValue) {
      seatBoardTotalValue.textContent = formatCurrency(total);
    }

    if (amountPerPoint > 0) {
      const estimatedPoints = Math.floor(total / amountPerPoint);
      loyaltyPreview.innerHTML = isMember
        ? (estimatedPoints > 0
            ? `Dự kiến cộng <strong>${estimatedPoints} điểm</strong> sau khi booking thanh toán thành công.`
            : 'Đơn hàng hiện chưa đủ điều kiện cộng điểm.')
        : (estimatedPoints > 0
            ? `Đăng nhập tài khoản thành viên để lưu booking và tích khoảng <strong>${estimatedPoints} điểm</strong>.`
            : 'Đăng nhập tài khoản thành viên để lưu lịch sử booking và tích điểm ở các đơn tiếp theo.');
    } else {
      loyaltyPreview.textContent = '';
    }

<<<<<<< HEAD
    syncHiddenInputs();
=======
    const invalidRows = findSingleGapRows(state.selectedSeatIds);
    if (invalidRows.length) {
      showAlert(`Cách chọn hiện tại để lại 1 ghế lẻ ở ${humanizeRowList(invalidRows)}. Hãy chọn thêm 1 ghế liền kề hoặc bỏ bớt để sơ đồ ngồi gọn hơn.`, 'error', 'seat-gap', 'Sắp xếp ghế chưa tối ưu');
    } else {
      clearAlert('seat-gap');
    }

    syncHiddenInputs();
    bookingSubmitButton.disabled = state.selectedSeatIds.length === 0 || state.isSyncing || invalidRows.length > 0;
>>>>>>> origin/main
    updateHoldBox();
  };

  const applySeatPayload = (seatPayload, selectedSeatIds = null) => {
    state.seats = Array.isArray(seatPayload) ? seatPayload : [];
    if (Array.isArray(selectedSeatIds)) {
      state.selectedSeatIds = selectedSeatIds.map(Number).filter(Boolean);
    } else {
      state.selectedSeatIds = state.seats.filter((seat) => seat.selected_by_self).map((seat) => Number(seat.id));
    }
    normalizeTicketTypeSelections();
    renderSeatMap();
    renderSelectedSeatAssignments();
    renderSummary();
  };

  const syncSelectedSeats = async ({ silent = false } = {}) => {
    if (!csrfToken) {
      return;
    }

    state.isSyncing = true;
    bookingSubmitButton.disabled = true;
    setLiveMessage('Đang cập nhật giữ ghế...');

    try {
      const response = await fetch(seatSyncUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken,
        },
        credentials: 'same-origin',
        body: JSON.stringify({ seat_ids: state.selectedSeatIds }),
      });

      const payload = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(payload.message || 'Không thể cập nhật giữ ghế.');
      }

<<<<<<< HEAD
      applySeatPayload(payload.seats || [], payload.selected_seat_ids || []);
      setLiveMessage(`Ghế đang được đồng bộ realtime mỗi ${seatPollSeconds} giây`);
      startHoldCountdown();
=======
      applyServerClock(payload.server_time);
      applyHoldExpiry(payload.owner_hold_expires_at);
      applySeatPayload(payload.seats || [], payload.selected_seat_ids || []);
      setLiveMessage(`Ghế đang được đồng bộ realtime mỗi ${seatPollSeconds} giây`);
      if (state.holdDeadlineAt) {
        startHoldCountdown();
      } else {
        stopHoldCountdown();
        state.holdDeadlineAt = null;
      }
>>>>>>> origin/main
      if (!silent) {
        clearAlert('seat-sync');
      }
    } catch (error) {
      setLiveMessage('Không thể đồng bộ ghế, đang thử lại...');
      showAlert(error.message || 'Không thể đồng bộ ghế. Vui lòng thử lại.', 'error', 'seat-sync');
      applySeatPayload(state.seats, state.selectedSeatIds);
    } finally {
      state.isSyncing = false;
<<<<<<< HEAD
      bookingSubmitButton.disabled = state.selectedSeatIds.length === 0;
=======
      renderSummary();
>>>>>>> origin/main
    }
  };

  const scheduleSeatSync = (options = {}) => {
    window.clearTimeout(state.syncTimer);
    state.syncTimer = window.setTimeout(() => syncSelectedSeats(options), 280);
  };

  const fetchSeatStatus = async () => {
    try {
      const response = await fetch(seatStatusUrl, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      });
      const payload = await response.json().catch(() => ({}));
      if (!response.ok) {
        return;
      }

      const previousSelection = new Set(state.selectedSeatIds.map(Number));
<<<<<<< HEAD
      applySeatPayload(payload.seats || []);
      if (state.selectedSeatIds.length) {
        startHoldCountdown();
      } else {
        stopHoldCountdown();
      }
      const removedSeats = Array.from(previousSelection).filter((seatId) => !state.selectedSeatIds.includes(Number(seatId)));
      if (removedSeats.length) {
        showAlert('Có ghế bạn chọn vừa bị thay đổi trạng thái. Danh sách ghế đã được làm mới theo thời gian thực.', 'info', `status-${removedSeats.join('-')}`);
      } else {
        clearAlert();
=======
      applyServerClock(payload.server_time);
      applyHoldExpiry(payload.owner_hold_expires_at);
      applySeatPayload(payload.seats || []);
      if (state.holdDeadlineAt) {
        startHoldCountdown();
      } else {
        stopHoldCountdown();
        state.holdDeadlineAt = null;
      }
      const removedSeats = Array.from(previousSelection).filter((seatId) => !state.selectedSeatIds.includes(Number(seatId)));
      if (removedSeats.length) {
        showAlert('Có ghế bạn chọn vừa bị thay đổi trạng thái. Danh sách ghế đã được làm mới theo thời gian thực.', 'info', `status-${removedSeats.join('-')}`, 'Sơ đồ ghế vừa được cập nhật');
      } else {
        clearAlert('seat-sync');
>>>>>>> origin/main
      }
      setLiveMessage(`Ghế đang được đồng bộ realtime mỗi ${seatPollSeconds} giây`);
    } catch (error) {
      setLiveMessage('Kết nối realtime tạm chậm, hệ thống sẽ tự thử lại');
    }
  };

  const toggleSeatSelection = (seatId) => {
    if (!seatId) return;
    const seat = getSeatById(seatId);
    if (!seat || !seat.available) return;

    let candidateSeatIds = [...state.selectedSeatIds];
    const alreadySelected = candidateSeatIds.includes(seatId);
<<<<<<< HEAD
    const pairSeat = findPairSeat(seat);

    if (alreadySelected) {
      candidateSeatIds = candidateSeatIds.filter((value) => value !== seatId);
      if (pairSeat) {
        candidateSeatIds = candidateSeatIds.filter((value) => value !== Number(pairSeat.id));
      }
    } else {
      if (candidateSeatIds.length >= maxSeats) {
        window.alert(`Bạn chỉ có thể chọn tối đa ${maxSeats} ghế trong một booking.`);
        return;
      }

      if (pairSeat) {
        if (!pairSeat.available && !state.selectedSeatIds.includes(Number(pairSeat.id))) {
          window.alert('Ghế đôi phải được chọn thành cặp liền nhau và hiện ghế còn lại không còn trống.');
          return;
        }

        if (!candidateSeatIds.includes(seatId)) {
          candidateSeatIds.push(seatId);
        }
        if (!candidateSeatIds.includes(Number(pairSeat.id))) {
          if (candidateSeatIds.length >= maxSeats) {
            window.alert(`Ghế đôi cần 2 chỗ. Bạn chỉ có thể chọn tối đa ${maxSeats} ghế.`);
            return;
          }
          candidateSeatIds.push(Number(pairSeat.id));
        }
      } else {
        candidateSeatIds.push(seatId);
      }
=======

    if (alreadySelected) {
      candidateSeatIds = candidateSeatIds.filter((value) => value !== seatId);
    } else {
      if (candidateSeatIds.length >= maxSeats) {
        showAlert(`Bạn chỉ có thể chọn tối đa ${maxSeats} ghế trong một booking.`, 'info', 'max-seats', 'Đã đạt giới hạn ghế');
        return;
      }

      candidateSeatIds.push(seatId);
>>>>>>> origin/main
    }

    candidateSeatIds = Array.from(new Set(candidateSeatIds.map(Number))).filter(Boolean);

    const invalidRows = findSingleGapRows(candidateSeatIds);
<<<<<<< HEAD
    if (invalidRows.length) {
      window.alert(`Cách chọn hiện tại để lại 1 ghế lẻ ở dãy ${invalidRows.join(', ')}. Vui lòng chọn lại để không chừa ghế đơn.`);
=======
    if (!alreadySelected && invalidRows.length) {
      showAlert(`Cách chọn hiện tại để lại 1 ghế lẻ ở ${humanizeRowList(invalidRows)}. Hãy chọn thêm 1 ghế liền kề hoặc bỏ bớt để sơ đồ ngồi gọn hơn.`, 'error', 'seat-gap', 'Sắp xếp ghế chưa tối ưu');
>>>>>>> origin/main
      return;
    }

    state.selectedSeatIds = candidateSeatIds;
    normalizeTicketTypeSelections();
    renderSeatMap();
    renderSelectedSeatAssignments();
    renderSummary();
<<<<<<< HEAD
    scheduleSeatSync();
  };

=======
    if (alreadySelected) {
      syncSelectedSeats({ silent: true });
    } else {
      scheduleSeatSync();
    }
  };

          ? Math.min(maxQty, currentQty + 1)
          : Math.max(0, currentQty - 1);
        renderProducts();
        renderSummary();
      });
    });

    productCatalog.querySelectorAll('[data-product-input]').forEach((input) => {
      input.addEventListener('input', () => {
        const productId = String(input.dataset.productId || '');
        const product = productMap[productId];
        if (!product) return;
        const maxQty = Math.min(20, Number(product.qty_on_hand || 0));
        state.selectedProductQty[productId] = Math.max(0, Math.min(maxQty, Number(input.value || 0)));
        renderProducts();
        renderSummary();
      });
    });
  };


>>>>>>> origin/main
  const releaseSeatsOnLeave = () => {
    if (!state.selectedSeatIds.length || !navigator.sendBeacon || !csrfToken) {
      return;
    }
<<<<<<< HEAD
    const formData = new FormData();
    formData.append('_token', csrfToken);
    navigator.sendBeacon(seatSyncUrl, formData);
  };

=======
    const blob = new Blob([JSON.stringify({ _token: csrfToken, seat_ids: [] })], { type: 'application/json' });
    navigator.sendBeacon(seatSyncUrl, blob);
  };


>>>>>>> origin/main
  form.addEventListener('submit', (event) => {
    if (!state.selectedSeatIds.length) {
      event.preventDefault();
      showAlert('Bạn cần chọn ít nhất một ghế trước khi tạo booking.', 'error', 'submit-empty');
      return;
    }

    const invalidRows = findSingleGapRows(state.selectedSeatIds);
    if (invalidRows.length) {
      event.preventDefault();
<<<<<<< HEAD
      showAlert(`Cách chọn ghế hiện tại để lại 1 ghế lẻ ở dãy ${invalidRows.join(', ')}. Vui lòng chọn lại.`, 'error', 'submit-gap');
=======
      showAlert(`Cách chọn ghế hiện tại để lại 1 ghế lẻ ở ${humanizeRowList(invalidRows)}. Vui lòng điều chỉnh trước khi sang bước thanh toán.`, 'error', 'submit-gap', 'Chưa thể sang bước thanh toán');
>>>>>>> origin/main
      return;
    }

    syncHiddenInputs();
    bookingSubmitButton.disabled = true;
    bookingSubmitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Đang tạo booking...';
  });

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      window.clearInterval(state.pollTimer);
      state.pollTimer = null;
      return;
    }
    fetchSeatStatus();
    if (!state.pollTimer) {
      state.pollTimer = window.setInterval(fetchSeatStatus, seatPollSeconds * 1000);
    }
  });

  window.addEventListener('beforeunload', releaseSeatsOnLeave);

<<<<<<< HEAD
  applySeatPayload(state.seats, state.selectedSeatIds);
  renderProducts();
  syncHiddenInputs();
  if (state.selectedSeatIds.length) {
=======
  applyHoldExpiry(bookingConfig.owner_hold_expires_at || null);
  applySeatPayload(state.seats, state.selectedSeatIds);
  syncHiddenInputs();
  if (state.holdDeadlineAt) {
>>>>>>> origin/main
    startHoldCountdown();
  }
  setLiveMessage(`Ghế đang được đồng bộ realtime mỗi ${seatPollSeconds} giây`);

  if (state.selectedSeatIds.length) {
    scheduleSeatSync({ silent: true });
  }
  state.pollTimer = window.setInterval(fetchSeatStatus, seatPollSeconds * 1000);
<<<<<<< HEAD
=======

>>>>>>> origin/main
})();
</script>
@endpush
