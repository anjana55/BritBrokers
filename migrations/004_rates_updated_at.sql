-- 004: Record when the exchange rates were last refreshed, so the control panel can show
-- whether the daily cron job (bin/update-exchange-rates.php) is running.
ALTER TABLE rates ADD COLUMN IF NOT EXISTS updated_at DATETIME NULL;
