@extends('layouts.base')

@section('title', 'Trauma-Informed Layout Lab')

@section('body-class', 'design-lab-page')

@section('body')
<main class="main design-lab-main" id="main-content" role="main">
    <div class="container">
        <section class="lab-hero">
            <div class="lab-hero-copy">
                <p class="lab-eyebrow">Trauma-Informed UI Lab</p>
                <h1>Three layout directions for a calmer, clearer Grounding Buddy</h1>
                <p class="lab-intro">
                    Each concept keeps the same model already present in the product: calm language, predictable next steps,
                    visible repair paths, and no shame-forward alerts. The difference is how the information is staged.
                </p>
            </div>
            <div class="lab-guardrails">
                <h2>Shared guardrails</h2>
                <ul class="guardrail-list">
                    <li>Lead with the next safe action, not the full system state.</li>
                    <li>Show what is paused, when it will be revisited, and how to repair.</li>
                    <li>Keep family-wide context available without forcing it into the primary flow.</li>
                </ul>
            </div>
        </section>

        <section class="recommendation-panel">
            <article class="recommendation-card">
                <p class="recommendation-label">Recommendation 1</p>
                <h2>Make the primary next step unmistakable</h2>
                <p>
                    The kid flow currently uses stacked cards with similar visual weight, so progress, chores, consequence details,
                    and navigation compete with each other. A dedicated "what to do now" zone would reduce cognitive load.
                </p>
            </article>
            <article class="recommendation-card">
                <p class="recommendation-label">Recommendation 2</p>
                <h2>Use progressive disclosure for high-context information</h2>
                <p>
                    Family schedule, history, and policy details are useful, but they should sit behind calmer reveals. That keeps
                    the surface predictable for kids while still giving caregivers the full context when needed.
                </p>
            </article>
            <article class="recommendation-card">
                <p class="recommendation-label">Recommendation 3</p>
                <h2>Reframe consequence moments around repair and timing</h2>
                <p>
                    The language is already strong. The next UX step is to consistently pair every paused privilege state with a
                    short explanation, a review time, and a concrete path back on track.
                </p>
            </article>
        </section>

        <section class="concept concept-focus">
            <div class="concept-header">
                <div>
                    <p class="concept-tag">Layout 1</p>
                    <h2>Gentle Focus</h2>
                </div>
                <p class="concept-philosophy">Single-column, one-step-at-a-time, strongest for kid daily use.</p>
            </div>

            <div class="concept-preview focus-preview">
                <section class="focus-hero-card">
                    <p class="mini-label">Today</p>
                    <h3>One calm step at a time</h3>
                    <p class="support-copy">You have 2 chores left. Start with kitchen reset, then submit a photo when you are ready.</p>
                    <div class="focus-progress">
                        <div class="focus-progress-bar" aria-hidden="true">
                            <span style="width: 66%"></span>
                        </div>
                        <span>2 of 3 complete</span>
                    </div>
                    <a href="#" class="btn btn-primary btn-lg">Start kitchen reset</a>
                </section>

                <section class="focus-support-strip">
                    <div class="support-pill">
                        <strong>Paused right now:</strong> games
                    </div>
                    <div class="support-pill">
                        <strong>Next review:</strong> today at 7:00 PM
                    </div>
                    <div class="support-pill">
                        <strong>Repair path:</strong> finish chores and check in calmly
                    </div>
                </section>

                <section class="focus-list-card">
                    <div class="preview-card-header">
                        <h3>Today&apos;s list</h3>
                        <span class="preview-note">Optional details stay secondary</span>
                    </div>
                    <div class="focus-task done">
                        <span class="task-mark">Done</span>
                        <div>
                            <strong>Feed the dog</strong>
                            <p>Approved this morning</p>
                        </div>
                    </div>
                    <div class="focus-task current">
                        <span class="task-mark">Now</span>
                        <div>
                            <strong>Kitchen reset</strong>
                            <p>Wipe counters, put dishes away, then submit proof</p>
                        </div>
                    </div>
                    <div class="focus-task">
                        <span class="task-mark">Later</span>
                        <div>
                            <strong>Trash to curb</strong>
                            <p>Available after 5:00 PM</p>
                        </div>
                    </div>
                </section>
            </div>
        </section>

        <section class="concept concept-circle">
            <div class="concept-header">
                <div>
                    <p class="concept-tag">Layout 2</p>
                    <h2>Circle of Support</h2>
                </div>
                <p class="concept-philosophy">iPhone-first relational layout with one clear next step and optional family context.</p>
            </div>

            <div class="concept-preview circle-preview">
                <div class="phone-frame" aria-label="iPhone layout preview">
                    <div class="phone-status-bar">
                        <span>9:41</span>
                        <span>LTE</span>
                    </div>

                    <section class="phone-hero">
                        <p class="mini-label">Today</p>
                        <h3>Hi Maya, you&apos;re on track</h3>
                        <p>One routine is left for today. Your family sees the same plan, so expectations stay predictable.</p>
                        <div class="phone-progress">
                            <div class="phone-progress-track" aria-hidden="true">
                                <span style="width: 70%"></span>
                            </div>
                            <span>2 of 3 complete</span>
                        </div>
                    </section>

                    <section class="phone-primary-card">
                        <div class="preview-card-header">
                            <h3>Next calm step</h3>
                            <span class="soft-badge">now</span>
                        </div>
                        <strong>Kitchen reset</strong>
                        <p>Wipe counters, put dishes away, then send a photo when you feel ready.</p>
                        <a href="#" class="btn btn-primary btn-block">Open checklist</a>
                    </section>

                    <section class="phone-chip-row" aria-label="Supportive status details">
                        <div class="phone-chip">
                            <strong>Paused</strong>
                            <span>Games</span>
                        </div>
                        <div class="phone-chip">
                            <strong>Review</strong>
                            <span>7:00 PM</span>
                        </div>
                        <div class="phone-chip">
                            <strong>Support</strong>
                            <span>Calm check-in</span>
                        </div>
                    </section>

                    <section class="phone-list-card">
                        <div class="preview-card-header">
                            <h3>My rhythm</h3>
                            <span class="soft-badge">steady</span>
                        </div>
                        <div class="phone-task done">
                            <span class="phone-task-mark">Done</span>
                            <div>
                                <strong>Feed dog</strong>
                                <p>Reviewed this morning</p>
                            </div>
                        </div>
                        <div class="phone-task current">
                            <span class="phone-task-mark">Now</span>
                            <div>
                                <strong>Kitchen reset</strong>
                                <p>About 10 minutes</p>
                            </div>
                        </div>
                        <div class="phone-task later">
                            <span class="phone-task-mark">Later</span>
                            <div>
                                <strong>Trash to curb</strong>
                                <p>Available after 5:00 PM</p>
                            </div>
                        </div>
                    </section>

                    <details class="phone-disclosure">
                        <summary>Family context</summary>
                        <div class="phone-disclosure-panel">
                            <div class="week-mini-grid" aria-hidden="true">
                                <span>Mon</span><span class="is-active">Tue</span><span>Wed</span><span>Thu</span><span>Fri</span>
                            </div>
                            <p class="mb-0">Shared context stays available without crowding the first screen.</p>
                        </div>
                    </details>

                    <details class="phone-disclosure calm-note">
                        <summary>Caregiver note</summary>
                        <div class="phone-disclosure-panel">
                            <p class="mb-0">Games can be reviewed tonight after chores are complete. Thanks for staying with the plan.</p>
                        </div>
                    </details>

                    <div class="phone-bottom-bar">
                        <a href="#" class="phone-nav-item active">Today</a>
                        <a href="#" class="phone-nav-item">Week</a>
                        <a href="#" class="phone-nav-item">Bonuses</a>
                        <a href="#" class="phone-nav-item">History</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="concept concept-pathways">
            <div class="concept-header">
                <div>
                    <p class="concept-tag">Layout 3</p>
                    <h2>Pathways Board</h2>
                </div>
                <p class="concept-philosophy">Action board with explicit choices, strongest for momentum and autonomy.</p>
            </div>

            <div class="concept-preview pathways-preview">
                <section class="pathways-intro">
                    <p class="mini-label">Choose your next move</p>
                    <h3>Three lanes, no guessing</h3>
                    <p>Separate what needs action now from what is waiting on review and what can happen later.</p>
                </section>

                <div class="pathways-columns">
                    <article class="path-column now">
                        <div class="path-column-header">
                            <h3>Now</h3>
                            <span>1 item</span>
                        </div>
                        <div class="path-card">
                            <strong>Kitchen reset</strong>
                            <p>Clear surfaces and submit a photo when done.</p>
                            <a href="#" class="btn btn-secondary">Open checklist</a>
                        </div>
                    </article>
                    <article class="path-column waiting">
                        <div class="path-column-header">
                            <h3>Waiting</h3>
                            <span>1 item</span>
                        </div>
                        <div class="path-card">
                            <strong>Dog feeding</strong>
                            <p>Already sent. Review is expected before dinner.</p>
                        </div>
                    </article>
                    <article class="path-column later">
                        <div class="path-column-header">
                            <h3>Later</h3>
                            <span>2 items</span>
                        </div>
                        <div class="path-card">
                            <strong>Trash to curb</strong>
                            <p>Unlocks after 5:00 PM.</p>
                        </div>
                        <div class="path-card support-card">
                            <strong>Back on track</strong>
                            <p>Games return after review if the day&apos;s work is complete.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </div>
</main>
@endsection

@push('head')
<style>
    .design-lab-page {
        --lab-ink: #183642;
        --lab-sand: #f7f1e8;
        --lab-mist: #eef4f3;
        --lab-rose: #f7e9e4;
        --lab-gold: #d5a25d;
        background:
            radial-gradient(circle at top left, rgba(124, 181, 134, 0.12), transparent 28%),
            linear-gradient(180deg, #f8fbfb 0%, #f3f7f6 100%);
    }

    .design-lab-main {
        padding-top: var(--space-10);
    }

    .lab-hero {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: var(--space-6);
        margin-bottom: var(--space-8);
        align-items: start;
    }

    .lab-eyebrow,
    .concept-tag,
    .recommendation-label,
    .mini-label {
        margin-bottom: var(--space-2);
        font-size: 0.78rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 700;
    }

    .lab-hero-copy h1 {
        max-width: 12ch;
        margin-bottom: var(--space-4);
        font-family: "Iowan Old Style", "Palatino Linotype", Georgia, serif;
        font-size: clamp(2.5rem, 5vw, 4.5rem);
        line-height: 0.98;
        color: var(--lab-ink);
    }

    .lab-intro,
    .concept-philosophy,
    .recommendation-card p,
    .guardrail-list,
    .support-copy,
    .path-card p,
    .timeline-line,
    .circle-banner p,
    .circle-rail-card p {
        color: var(--text-secondary);
    }

    .lab-guardrails {
        padding: var(--space-6);
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(24, 54, 66, 0.08);
        border-radius: 28px;
        box-shadow: 0 18px 40px rgba(24, 54, 66, 0.08);
    }

    .guardrail-list {
        margin: 0;
        padding-left: 1.25rem;
    }

    .recommendation-panel {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: var(--space-4);
        margin-bottom: var(--space-10);
    }

    .recommendation-card,
    .concept {
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid rgba(24, 54, 66, 0.08);
        border-radius: 30px;
        box-shadow: 0 20px 45px rgba(39, 73, 84, 0.08);
        backdrop-filter: blur(16px);
    }

    .recommendation-card {
        padding: var(--space-6);
    }

    .recommendation-card h2 {
        font-size: 1.3rem;
    }

    .concept {
        padding: var(--space-6);
        margin-bottom: var(--space-8);
    }

    .concept-header {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: var(--space-4);
        margin-bottom: var(--space-5);
    }

    .concept-header h2 {
        margin-bottom: 0;
        font-size: 2rem;
    }

    .concept-philosophy {
        max-width: 24rem;
        margin-bottom: 0;
        text-align: right;
    }

    .concept-preview {
        min-height: 420px;
    }

    .preview-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-3);
        margin-bottom: var(--space-4);
    }

    .preview-note,
    .soft-badge {
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.72);
        color: var(--text-secondary);
        font-size: 0.8rem;
        font-weight: 600;
    }

    .focus-preview {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: var(--space-5);
    }

    .focus-hero-card {
        padding: var(--space-6);
        border-radius: 28px;
        background: linear-gradient(160deg, #ffffff 0%, #f3f8f7 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
    }

    .focus-hero-card h3,
    .focus-list-card h3,
    .circle-banner h3,
    .circle-card h3,
    .circle-rail-card h3,
    .pathways-intro h3,
    .path-column h3 {
        font-family: "Avenir Next Rounded", "Trebuchet MS", "Segoe UI", sans-serif;
    }

    .focus-progress {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin: var(--space-5) 0;
    }

    .focus-progress-bar {
        flex: 1;
        height: 14px;
        border-radius: 999px;
        background: #dfe9e6;
        overflow: hidden;
    }

    .focus-progress-bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #8acb97 0%, #4a90a4 100%);
    }

    .focus-support-strip {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-3);
        grid-column: 1 / -1;
    }

    .support-pill {
        padding: 0.85rem 1rem;
        border-radius: 999px;
        background: rgba(232, 167, 86, 0.14);
        color: #7b5a26;
        font-size: 0.92rem;
    }

    .focus-list-card {
        padding: var(--space-5);
        border-radius: 28px;
        background: rgba(250, 251, 252, 0.9);
    }

    .focus-task {
        display: flex;
        gap: var(--space-3);
        align-items: start;
        padding: 1rem 0;
        border-top: 1px solid rgba(24, 54, 66, 0.08);
    }

    .focus-task:first-of-type {
        border-top: 0;
        padding-top: 0;
    }

    .task-mark {
        min-width: 3.5rem;
        padding: 0.35rem 0.55rem;
        border-radius: 999px;
        background: #edf3f2;
        font-size: 0.78rem;
        font-weight: 700;
        text-align: center;
        color: var(--lab-ink);
    }

    .focus-task.done .task-mark {
        background: rgba(124, 181, 134, 0.18);
    }

    .focus-task.current .task-mark {
        background: rgba(74, 144, 164, 0.18);
    }

    .focus-task p,
    .circle-stack,
    .pathways-intro p {
        margin-bottom: 0;
    }

    .circle-preview {
        display: flex;
        justify-content: center;
        background: linear-gradient(135deg, rgba(247, 241, 232, 0.85), rgba(238, 244, 243, 0.95));
        border-radius: 28px;
        padding: var(--space-5);
    }

    .phone-frame {
        width: min(100%, 390px);
        min-height: 844px;
        padding: 1rem 1rem calc(88px + var(--safe-area-bottom));
        border-radius: 36px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(246, 250, 249, 0.98) 100%);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.96),
            0 24px 50px rgba(24, 54, 66, 0.16);
        position: relative;
        overflow: hidden;
    }

    .phone-status-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: var(--space-4);
        padding: 0.15rem 0.3rem 0;
        color: var(--lab-ink);
        font-size: 0.82rem;
        font-weight: 700;
    }

    .phone-hero {
        padding: 1.1rem;
        border-radius: 24px;
        background: linear-gradient(160deg, rgba(255, 255, 255, 0.95), rgba(236, 244, 242, 0.95));
        box-shadow: 0 12px 24px rgba(56, 85, 79, 0.07);
    }

    .phone-hero h3,
    .phone-primary-card h3,
    .phone-list-card h3,
    .phone-disclosure summary {
        font-family: "Avenir Next Rounded", "Trebuchet MS", "Segoe UI", sans-serif;
    }

    .phone-progress {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1rem;
        color: var(--text-secondary);
        font-size: 0.88rem;
    }

    .phone-progress-track {
        flex: 1;
        height: 10px;
        border-radius: 999px;
        background: #dfe8e6;
        overflow: hidden;
    }

    .phone-progress-track span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #7cb586 0%, #4a90a4 100%);
        border-radius: inherit;
    }

    .phone-primary-card,
    .phone-list-card,
    .phone-disclosure {
        margin-top: var(--space-4);
    }

    .phone-primary-card,
    .phone-list-card {
        padding: 1rem;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 12px 24px rgba(56, 85, 79, 0.06);
    }

    .phone-chip-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-top: var(--space-4);
    }

    .phone-chip {
        padding: 0.85rem 0.75rem;
        border-radius: 20px;
        background: rgba(232, 167, 86, 0.14);
        text-align: center;
        color: #6e562f;
    }

    .phone-chip strong,
    .phone-chip span {
        display: block;
    }

    .phone-chip strong {
        font-size: 0.74rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .phone-chip span {
        margin-top: 0.2rem;
        font-weight: 600;
        font-size: 0.92rem;
    }

    .phone-task {
        display: flex;
        gap: 0.75rem;
        align-items: start;
        padding: 0.9rem 0;
        border-top: 1px solid rgba(24, 54, 66, 0.08);
    }

    .phone-task:first-of-type {
        border-top: 0;
        padding-top: 0;
    }

    .phone-task-mark {
        min-width: 3.4rem;
        padding: 0.35rem 0.5rem;
        border-radius: 999px;
        background: #edf3f2;
        color: var(--lab-ink);
        text-align: center;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .phone-task.done .phone-task-mark {
        background: rgba(124, 181, 134, 0.18);
    }

    .phone-task.current .phone-task-mark {
        background: rgba(74, 144, 164, 0.18);
    }

    .phone-task.later .phone-task-mark {
        background: rgba(213, 162, 93, 0.16);
    }

    .phone-task p {
        margin-bottom: 0;
        color: var(--text-secondary);
    }

    .phone-disclosure {
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.84);
        box-shadow: 0 12px 24px rgba(56, 85, 79, 0.05);
        overflow: hidden;
    }

    .phone-disclosure summary {
        list-style: none;
        cursor: pointer;
        padding: 1rem;
        font-weight: 700;
        color: var(--lab-ink);
    }

    .phone-disclosure summary::-webkit-details-marker {
        display: none;
    }

    .phone-disclosure-panel {
        padding: 0 1rem 1rem;
    }

    .phone-bottom-bar {
        position: absolute;
        left: 0.75rem;
        right: 0.75rem;
        bottom: 0.75rem;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.35rem;
        padding: 0.55rem;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 12px 24px rgba(24, 54, 66, 0.12);
    }

    .phone-nav-item {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        border-radius: 16px;
        color: var(--text-secondary);
        font-size: 0.82rem;
        font-weight: 700;
    }

    .phone-nav-item.active {
        background: rgba(74, 144, 164, 0.14);
        color: var(--primary-dark);
    }

    .circle-banner,
    .circle-card,
    .circle-rail-card,
    .path-card,
    .pathways-intro {
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.84);
        padding: var(--space-5);
        box-shadow: 0 14px 30px rgba(54, 71, 67, 0.07);
    }

    .week-mini-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.5rem;
        margin: var(--space-4) 0;
    }

    .week-mini-grid span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2.75rem;
        border-radius: 16px;
        background: var(--lab-mist);
        color: var(--lab-ink);
        font-weight: 600;
    }

    .week-mini-grid .is-active {
        background: #4a90a4;
        color: white;
    }

    .calm-note {
        background: rgba(247, 233, 228, 0.72);
    }

    .pathways-preview {
        background: linear-gradient(180deg, #16313d 0%, #214554 100%);
        color: white;
        border-radius: 30px;
        padding: var(--space-5);
    }

    .pathways-preview .mini-label,
    .pathways-preview p {
        color: rgba(255, 255, 255, 0.78);
    }

    .pathways-columns {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: var(--space-4);
        margin-top: var(--space-5);
    }

    .path-column {
        padding: var(--space-4);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .path-column-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: var(--space-4);
    }

    .path-column-header span {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.72);
    }

    .path-card {
        color: var(--lab-ink);
        margin-bottom: var(--space-3);
    }

    .path-card:last-child {
        margin-bottom: 0;
    }

    .support-card {
        background: rgba(232, 167, 86, 0.16);
    }

    .path-column.now {
        background: rgba(123, 181, 134, 0.14);
    }

    .path-column.waiting {
        background: rgba(74, 144, 164, 0.16);
    }

    .path-column.later {
        background: rgba(255, 255, 255, 0.06);
    }

    @media (max-width: 960px) {
        .lab-hero,
        .recommendation-panel,
        .focus-preview,
        .pathways-columns,
        .circle-preview {
            grid-template-columns: 1fr;
        }

        .concept-header {
            align-items: start;
            flex-direction: column;
        }

        .concept-philosophy {
            text-align: left;
        }
    }

    @media (max-width: 640px) {
        .design-lab-main {
            padding-top: var(--space-6);
        }

        .recommendation-card,
        .concept,
        .circle-preview,
        .pathways-preview,
        .focus-hero-card,
        .focus-list-card {
            border-radius: 24px;
        }

        .support-pill {
            border-radius: 18px;
        }

        .phone-frame {
            width: 100%;
            min-height: auto;
            padding-left: 0.85rem;
            padding-right: 0.85rem;
        }

        .phone-chip-row {
            grid-template-columns: 1fr;
        }

        .phone-bottom-bar {
            position: static;
            margin-top: var(--space-4);
        }
    }
</style>
@endpush
