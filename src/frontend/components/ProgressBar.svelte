<script>
  export let progress = 0
  export let current = 1
  export let total = 1
</script>

<div class="ze-progress-bar">
  <div class="ze-progress-fill" style="width: {progress}%"></div>
</div>

<div class="ze-progress-text">
  Frage {current} von {total}
</div>

<style>
  .ze-progress-bar {
    width: 100%;
    height: 8px;
    background: #f3f4f6;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
  }

  .ze-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 4px;
    transition: width 0.3s ease-out;
  }

  .ze-progress-text {
    text-align: center;
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 24px;
  }
</style>