<div class="network-cv">
    <header class="cv-header">
        <div class="header-left">
            <h1><?= htmlspecialchars($personal_info['full_name']) ?></h1>
            <h2><?= htmlspecialchars($personal_info['title']) ?></h2>
        </div>
        <div class="header-right">
            <p><?= htmlspecialchars($personal_info['email']) ?></p>
            <p><?= htmlspecialchars($personal_info['phone']) ?></p>
            <p><?= htmlspecialchars($personal_info['address']) ?></p>
        </div>
    </header>
    
    <div class="cv-grid">
        <section class="experience-section">
            <h3>Expérience Réseau</h3>
            <?php foreach ($experiences as $exp): ?>
            <div class="experience-item">
                <h4><?= htmlspecialchars($exp['position']) ?></h4>
                <p class="company"><?= htmlspecialchars($exp['company']) ?></p>
                <p class="period"><?= htmlspecialchars($exp['start_date']) ?> - <?= htmlspecialchars($exp['end_date']) ?></p>
                <ul class="responsibilities">
                    <?php foreach ($exp['responsibilities'] as $resp): ?>
                    <li><?= htmlspecialchars($resp) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </section>
        
        <section class="certifications-section">
            <h3>Certifications</h3>
            <ul class="certifications-list">
                <?php foreach ($certifications as $cert): ?>
                <li>
                    <strong><?= htmlspecialchars($cert['name']) ?></strong>
                    <span><?= htmlspecialchars($cert['organization']) ?> (<?= htmlspecialchars($cert['year']) ?>)</span>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>
</div>

<style>
.network-cv {
    font-family: 'Arial', sans-serif;
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem;
    background: white;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.cv-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--primary-color);
}

.header-left h1 {
    color: var(--primary-color);
    font-size: 2.2rem;
    margin-bottom: 0.5rem;
}

.cv-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.experience-item {
    margin-bottom: 2rem;
}

.experience-item h4 {
    color: var(--primary-color);
    margin-bottom: 0.3rem;
}

.company {
    font-weight: bold;
    margin-bottom: 0.3rem;
}

.period {
    color: #666;
    font-style: italic;
    margin-bottom: 0.5rem;
}

.responsibilities {
    margin-left: 1.5rem;
}

.responsibilities li {
    margin-bottom: 0.3rem;
}

.certifications-list li {
    margin-bottom: 1rem;
    padding-left: 1rem;
    border-left: 3px solid var(--primary-color);
}

@media (max-width: 768px) {
    .cv-header {
        flex-direction: column;
    }
    
    .cv-grid {
        grid-template-columns: 1fr;
    }
}
</style>