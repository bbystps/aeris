import pymysql
import datetime
import pytz
import time

# Database connection
conn = pymysql.connect(
    host='localhost',
    user='root',
    password='ICPHpass!',
    database='aeris'
)
cursor = conn.cursor()

# Configuration
TIMEZONE = pytz.timezone('Asia/Singapore')
OFFLINE_THRESHOLD_MINUTES = 10

# Get all lamp names from the lamp_list table
def get_lamp_list():
    cursor.execute("SELECT name FROM lamp_list")
    return [row[0] for row in cursor.fetchall()]

# Convert lamp name to table name format (e.g., "Lamp 1" -> "lamp1_data")
def get_sensor_table_name(lamp_name):
    return lamp_name.lower().replace(" ", "") + "_data"

# Get the latest timestamp for each lamp's sensor data
def get_latest_timestamp(table_name):
    try:
        cursor.execute(f"SELECT timestamp FROM `{table_name}` ORDER BY id DESC LIMIT 1")
        result = cursor.fetchone()
        return datetime.datetime.strptime(str(result[0]), '%Y-%m-%d %H:%M:%S') if result else None
    except Exception as e:
        print(f"[ERROR] Failed to get timestamp from {table_name}: {e}")
        return None

# Update lamp status
def update_lamp_status(lamp_name, status):
    try:
        cursor.execute("UPDATE lamp_list SET status = %s WHERE name = %s", (status, lamp_name))
        conn.commit()
        print(f"[INFO] Status of {lamp_name} updated to {status}")
    except Exception as e:
        print(f"[ERROR] Failed to update status for {lamp_name}: {e}")
        conn.rollback()

def monitor_lamps():
    print("[INFO] Monitoring lamps...")
    lamps = get_lamp_list()
    now = datetime.datetime.now(TIMEZONE)

    for lamp in lamps:
        sensor_table = get_sensor_table_name(lamp)
        last_time = get_latest_timestamp(sensor_table)

        if last_time:
            last_time = TIMEZONE.localize(last_time)
            time_diff = (now - last_time).total_seconds() / 60.0

            if time_diff > OFFLINE_THRESHOLD_MINUTES:
                update_lamp_status(lamp, "Offline")
            else:
                update_lamp_status(lamp, "Online")
        else:
            print(f"[WARN] No data found for {lamp}, setting to Offline.")
            update_lamp_status(lamp, "Offline")

# Run it in a loop every minute
if __name__ == "__main__":
    while True:
        monitor_lamps()
        time.sleep(60)
