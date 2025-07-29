<div class="webmaster-cv">
    <header class="cv-header">
        <h1><?= htmlspecialchars($personal_info['full_name']) ?></h1>
        <h2><?= htmlspecialchars($personal_info['title']) ?></h2>
        <div class="contact-info">
            <p><?= htmlspecialchars($personal_info['email']) ?></p>
            <p><?= htmlspecialchars($personal_info['phone']) ?></p>
            <p><?= htmlspecialchars($personal_info['address']) ?></p>
        </div>
    </header>
    
    <section class="cv-section">
        <h3>Expériences Professionnelles</h3>
        <?php foreach ($experiences as $exp): ?>
        <div class="experience-item">
            <div class="experience-header">
                <h4><?= htmlspecialchars($exp['position']) ?></h4>
                <span class="experience-period"><?= htmlspecialchars($exp['start_date']) ?> - <?= htmlspecialchars($exp['end_date']) ?></span>
            </div>
            <p class="experience-company"><?= htmlspecialchars($exp['company']) ?></p>
            <p class="experience-description"><?= htmlspecialchars($exp['description']) ?></p>
        </div>
        <?php endforeach; ?>
    </section>
    
    <section class="cv-section">
        <h3>Formation</h3>
        <?php foreach ($education as $edu): ?>
        <div class="education-item">
            <h4><?= htmlspecialchars($edu['degree']) ?></h4>
            <p><?= htmlspecialchars($edu['institution']) ?> - <?= htmlspecialchars($edu['year']) ?></p>
        </div>
        <?php endforeach; ?>
    </section>
    
    <section class="cv-section skills-section">
        <h3>Compétences</h3>
        <div class="skills-grid">
            <?php foreach ($skills['technical'] as $skill): ?>
            <div class="skill-item">
                <span class="skill-name"><?= htmlspecialchars($skill['name']) ?></span>
                <div class="skill-level">
                    <div class="level-bar" style="width: <?= $skill['level'] ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<style>
.webmaster-cv {
    font-family: 'Arial', sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
    background: white;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.cv-header {
    text-align: center;
    margin-bottom: 2rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 1rem;
}

.cv-header h1 {
    color: var(--primary-color);
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.contact-info {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.cv-section {
    margin-bottom: 2rem;
}

.cv-section h3 {
    color: var(--primary-color);
    border-bottom: 1px solid #eee;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

.experience-item {
    margin-bottom: 1.5rem;
}

.experience-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.experience-period {
    color: #666;
}

.skills-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.skill-item {
    margin-bottom: 0.5rem;
}

.skill-level {
    height: 8px;
    background: #eee;
    border-radius: 4px;
    margin-top: 0.3rem;
}

.level-bar {
    height: 100%;
    background: var(--primary-color);
    border-radius: 4px;
}
</style>