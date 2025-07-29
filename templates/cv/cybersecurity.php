<?php
$personal_info = json_decode($cv['personal_info'], true);
$experiences = json_decode($cv['experiences'], true);
?>
<div class="cybersecurity-cv">
    <header class="cv-header">
        <div class="hacker-effect" data-text="<?= htmlspecialchars($personal_info['full_name']) ?>">
            <!-- Effet de texte type hacker -->
        </div>
        <div class="terminal-line">
            <span class="prompt">$></span>
            <span class="command"><?= htmlspecialchars($personal_info['title']) ?></span>
        </div>
    </header>
    
    <div class="cv-grid">
        <section class="personal-info">
            <h3><span class="hex">0x01</span> INFO</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">EMAIL:</span>
                    <span class="value"><?= htmlspecialchars($personal_info['email']) ?></span>
                </div>
                <!-- Autres informations -->
            </div>
        </section>
        
        <section class="experiences">
            <h3><span class="hex">0x02</span> EXP</h3>
            <?php foreach ($experiences as $exp): ?>
            <div class="exp-item">
                <div class="exp-header">
                    <span class="exp-title"><?= htmlspecialchars($exp['position']) ?></span>
                    <span class="exp-period">[<?= htmlspecialchars($exp['start_date']) ?>-<?= htmlspecialchars($exp['end_date']) ?>]</span>
                </div>
                <div class="exp-company">@<?= htmlspecialchars($exp['company']) ?></div>
                <div class="exp-desc"><?= htmlspecialchars($exp['description']) ?></div>
            </div>
            <?php endforeach; ?>
        </section>
    </div>
</div>