import pymysql
import paho.mqtt.client as mqtt
import json
import datetime
import pytz
import time

conn = pymysql.connect(
    host='localhost',
    user='root',
    password='ICPHpass!',
    database='aeris'
)

cursor = conn.cursor()

def on_connect(client, userdata, flags, rc):
    print(f"Connected with result code {rc}")
    client.subscribe("aeris/sensorData/#")
    client.subscribe("aeris/footTraffic/#")
    client.subscribe("aeris/map/#")

def on_disconnect(client, userdata, rc):
    print(f"Disconnected from MQTT broker with result code {rc}. Attempting to reconnect...")
    while True:
        try:
            client.reconnect()
            print("Reconnected successfully.")
            break
        except Exception as e:
            print(f"Reconnection failed: {e}. Retrying in 5 seconds...")
            time.sleep(5)

def on_message(client, userdata, message):
    print(f"Message received on topic: {message.topic}")
    msg_main = str(message.payload.decode("utf-8"))
    print(f"Received message on topic {message.topic}: {msg_main}")
    if message.topic.startswith("aeris/sensorData/"):
        print("Sensor data received")
        process_sensor_data(message.topic, msg_main)
    elif message.topic.startswith("aeris/footTraffic/"):
        print("Foot traffic data received")
        process_foot_traffic(message.topic, msg_main)
    elif message.topic.startswith("aeris/map/"):
        print("Map data received")
        update_map(message.topic, msg_main)

def process_sensor_data(topic, msg_main):
    try:
        parts = topic.split("/")
        if len(parts) == 3 and parts[0] == "aeris":
            SensorID = parts[2]
            print(f"SensorID: {SensorID}")
        else:
            print("Invalid topic format.")
            return
        
        json_msg_main = json.loads(msg_main)
        temperature = json_msg_main["T"]
        humidity = float(json_msg_main["H"])
        pm25 = float(json_msg_main["PM2.5"])
        pm10 = float(json_msg_main["PM10"])
        status = json_msg_main["S"]

        print(f"Temperature: {temperature}, Humidity: {humidity}, PM2.5: {pm25}, PM10: {pm10}, Status: {status}")
        insert_sensor_data(SensorID, temperature, humidity, pm25, pm10, status)
        update_status_if_changed(SensorID, status)

    except json.JSONDecodeError as e:
        print(f"Failed to decode JSON: {e}")
    except KeyError as e:
        print(f"Missing key in JSON data: {e}")
    except TypeError as e:
        print(f"Unexpected data type: {e}")
    except Exception as e:
        print(f"An unexpected error occurred: {e}")

def insert_sensor_data(SensorID, temperature, humidity, pm25, pm10, status):
    gmt8_time = datetime.datetime.now(pytz.timezone('Asia/Singapore'))
    timestamp = gmt8_time.strftime('%Y-%m-%d %H:%M:%S')
    SensorTable = SensorID + "_data"
    try:
        insert_query = f"INSERT INTO `{SensorTable}` (temperature, humidity, pm25, pm10, timestamp) VALUES (%s, %s, %s, %s, %s)"
        cursor.execute(insert_query, (temperature, humidity, pm25, pm10, timestamp))
        conn.commit()

        print(f"Insert Success for {SensorID}")
        client.publish("AERIS/DataUpdate", SensorID)
    except Exception as e:
        print("An error occurred:", e)
        conn.rollback()

def update_status_if_changed(sensor_id, new_status):
    try:
        table_name = sensor_id + "_status"
        # Get the most recent status
        query = f"SELECT status FROM `{table_name}` ORDER BY id DESC LIMIT 1"
        cursor.execute(query)
        result = cursor.fetchone()

        if result is None or result[0] != new_status:
            timestamp = datetime.datetime.now(pytz.timezone('Asia/Singapore')).strftime('%Y-%m-%d %H:%M:%S')
            insert_query = f"INSERT INTO `{table_name}` (status, timestamp) VALUES (%s, %s)"
            cursor.execute(insert_query, (new_status, timestamp))
            conn.commit()
            print(f"Status updated for {sensor_id}: {new_status}")
        else:
            print(f"No status change for {sensor_id}. Skipping insert.")
    except Exception as e:
        print(f"Failed to update status: {e}")
        conn.rollback()

def process_foot_traffic(topic, msg_main):
    try:
        parts = topic.split("/")
        if len(parts) == 3 and parts[0] == "aeris":
            SensorID = parts[2]
            print(f"SensorID: {SensorID}")
        else:
            print("Invalid topic format.")
            return
        json_msg_main = json.loads(msg_main)
        foot_traffic = json_msg_main["FT"]

        print(f"Foot Traffic: {foot_traffic}")
        insert_foot_traffic_data(SensorID, foot_traffic)

    except json.JSONDecodeError as e:
        print(f"Failed to decode JSON: {e}")
    except KeyError as e:
        print(f"Missing key in JSON data: {e}")
    except TypeError as e:
        print(f"Unexpected data type: {e}")
    except Exception as e:
        print(f"An unexpected error occurred: {e}")

def insert_foot_traffic_data(SensorID, foot_traffic):
    gmt8_time = datetime.datetime.now(pytz.timezone('Asia/Singapore'))
    timestamp = gmt8_time.strftime('%Y-%m-%d %H:%M:%S')
    SensorTable = SensorID + "_foot_traffic"
    try:
        insert_query = f"INSERT INTO `{SensorTable}` (value, timestamp) VALUES (%s, %s)"
        cursor.execute(insert_query, (foot_traffic, timestamp))
        conn.commit()

        print(f"Insert Success for {SensorID}")
        client.publish("AERIS/FootTrafficUpdate", SensorID)
    except Exception as e:
        print("An error occurred:", e)
        conn.rollback()

def update_map(topic, msg_main):
    try:
        parts = topic.split("/")
        if len(parts) == 3 and parts[0] == "aeris":
            # Normalize the lamp name from "lamp1" to "Lamp 1"
            lamp_name = parts[2]
            formatted_lamp_name = lamp_name.replace("lamp", "Lamp ").capitalize()
            print(f"Formatted Lamp Name: {formatted_lamp_name}")
        else:
            print("Invalid topic format.")
            return

        # Parse the received JSON message
        json_msg = json.loads(msg_main)
        longitude = str(json_msg.get("LNG"))
        latitude = str(json_msg.get("LAT"))

        if longitude and latitude:
            # Update the longitude and latitude in the database
            update_lamp_coordinates(formatted_lamp_name, longitude, latitude)
        else:
            print("Missing longitude or latitude data.")

    except json.JSONDecodeError as e:
        print(f"Failed to decode JSON: {e}")
    except KeyError as e:
        print(f"Missing key in JSON data: {e}")
    except Exception as e:
        print(f"An unexpected error occurred: {e}")

def update_lamp_coordinates(lamp_name, longitude, latitude):
    try:
        # Log the update to ensure the function is running
        print(f"Updating coordinates for {lamp_name}: Longitude={longitude}, Latitude={latitude}")
        
        # Prepare the update query
        update_query = f"UPDATE `lamp_list` SET `longitude` = %s, `latitude` = %s WHERE `name` = %s"
        
        # Execute the query
        cursor.execute(update_query, (longitude, latitude, lamp_name))
        conn.commit()

        # Check if the row was affected (i.e., updated)
        if cursor.rowcount > 0:
            print(f"Updated coordinates for {lamp_name} successfully.")
            client.publish("AERIS/MapUpdate", lamp_name)
        else:
            print(f"No record found for {lamp_name}. No update was made.")

    except Exception as e:
        print(f"Failed to update coordinates for {lamp_name}: {e}")
        conn.rollback()


client = mqtt.Client()

client.on_connect = on_connect
client.on_disconnect = on_disconnect
client.on_message = on_message

username = "mqtt"
password = "ICPHmqtt!"
client.username_pw_set(username, password)

try:
    client.connect("13.214.212.87", 1883, keepalive=60)
except Exception as e:
    print(f"Failed to connect to MQTT broker: {e}")
    exit(1)

client.loop_forever()