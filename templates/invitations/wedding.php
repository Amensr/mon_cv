<div class="wedding-invitation">
    <div class="invitation-header">
        <h1>Mariage</h1>
        <h2><?= htmlspecialchars($content['names']) ?></h2>
    </div>
    
    <div class="invitation-body">
        <p class="invitation-text"><?= nl2br(htmlspecialchars($content['message'])) ?></p>
        
        <div class="details">
            <div class="detail-item">
                <span class="icon">📅</span>
                <span><?= htmlspecialchars($content['date']) ?></span>
            </div>
            <div class="detail-item">
                <span class="icon">⏰</span>
                <span><?= htmlspecialchars($content['time']) ?></span>
            </div>
            <div class="detail-item">
                <span class="icon">📍</span>
                <span><?= htmlspecialchars($content['location']) ?></span>
            </div>
        </div>
        
        <div class="rsvp">
            <p>RSVP avant le <?= htmlspecialchars($content['rsvp_date']) ?></p>
            <p><?= htmlspecialchars($content['contact']) ?></p>
        </div>
    </div>
</div>

<style>
.wedding-invitation {
    max-width: 600px;
    margin: 0 auto;
    padding: 2rem;
    background: #fff9f9;
    border: 1px solid #f0d6d6;
    text-align: center;
    font-family: 'Times New Roman', serif;
}

.invitation-header {
    margin-bottom: 2rem;
    border-bottom: 1px solid #f0d6d6;
    padding-bottom: 1rem;
}

.invitation-header h1 {
    font-size: 2.5rem;
    color: #d4a5a5;
    margin-bottom: 0.5rem;
}

.invitation-header h2 {
    font-size: 1.8rem;
    color: #333;
}

.invitation-text {
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 2rem;
}

.details {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.1rem;
}

.rsvp {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #f0d6d6;
    font-style: italic;
}

@media print {
    .wedding-invitation {
        border: none;
        padding: 0;
    }
}
</style>