<script>
  export let funnelData

  const settings = funnelData?.settings || {}
</script>

<div class="ze-thank-you ze-fade-in">
  <div class="ze-thank-you-icon">
    🎉
  </div>

  <h2 class="ze-thank-you-title">
    {settings.thankYouTitle || 'Vielen Dank!'}
  </h2>

  <p class="ze-thank-you-message">
    {settings.thankYouMessage || 'Ihre Antworten wurden erfolgreich übermittelt. Wir werden uns bald bei Ihnen melden.'}
  </p>

  {#if settings.thankYouRedirectUrl}
    <div class="ze-thank-you-actions">
      <a 
        href={settings.thankYouRedirectUrl} 
        class="ze-btn ze-btn-primary"
        target={settings.thankYouRedirectTarget || '_self'}
      >
        {settings.thankYouRedirectText || 'Weiter'}
      </a>
    </div>
  {/if}
</div>

<style>
  .ze-thank-you {
    text-align: center;
    padding: 48px 20px;
  }

  .ze-thank-you-icon {
    font-size: 64px;
    margin-bottom: 24px;
    animation: bounce 0.6s ease-in-out;
  }

  .ze-thank-you-title {
    font-size: 32px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 16px;
  }

  .ze-thank-you-message {
    font-size: 18px;
    color: #6b7280;
    margin-bottom: 32px;
    line-height: 1.6;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
  }

  .ze-thank-you-actions {
    margin-top: 32px;
  }

  .ze-btn {
    padding: 12px 32px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease-in-out;
  }

  .ze-btn-primary {
    background: #3b82f6;
    color: white;
  }

  .ze-btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  }

  @keyframes bounce {
    0%, 20%, 60%, 100% { transform: translateY(0); }
    40% { transform: translateY(-20px); }
    80% { transform: translateY(-10px); }
  }

  @keyframes ze-fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .ze-fade-in {
    animation: ze-fade-in 0.5s ease-out;
  }

  @media (max-width: 768px) {
    .ze-thank-you {
      padding: 32px 16px;
    }
    
    .ze-thank-you-title {
      font-size: 24px;
    }
    
    .ze-thank-you-message {
      font-size: 16px;
    }
  }
</style>