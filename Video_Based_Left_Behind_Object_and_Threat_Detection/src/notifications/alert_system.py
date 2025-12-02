"""
Alert and Notification System
Sends notifications for left-behind objects and threats
"""

import smtplib
import logging
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.image import MIMEImage
from typing import List, Dict, Optional
from datetime import datetime
import os
from pathlib import Path

logger = logging.getLogger(__name__)


class AlertSystem:
    """
    Manages all alert notifications for the security system
    """
    
    def __init__(
        self,
        smtp_server: str,
        smtp_port: int,
        smtp_username: str,
        smtp_password: str,
        from_email: str
    ):
        """
        Initialize alert system
        
        Args:
            smtp_server: SMTP server address
            smtp_port: SMTP port (usually 587 for TLS)
            smtp_username: SMTP username
            smtp_password: SMTP password
            from_email: Sender email address
        """
        self.smtp_server = smtp_server
        self.smtp_port = smtp_port
        self.smtp_username = smtp_username
        self.smtp_password = smtp_password
        self.from_email = from_email
        
        # Alert cooldown tracking
        self.last_alert_times: Dict[str, datetime] = {}
        
    def send_email(
        self,
        to_emails: List[str],
        subject: str,
        body: str,
        image_path: Optional[str] = None
    ) -> bool:
        """
        Send email notification
        
        Args:
            to_emails: List of recipient email addresses
            subject: Email subject
            body: Email body (HTML supported)
            image_path: Optional path to image attachment
            
        Returns:
            True if successful
        """
        try:
            # Create message
            msg = MIMEMultipart()
            msg['From'] = self.from_email
            msg['To'] = ', '.join(to_emails)
            msg['Subject'] = subject
            
            # Add body
            msg.attach(MIMEText(body, 'html'))
            
            # Add image if provided
            if image_path and os.path.exists(image_path):
                with open(image_path, 'rb') as f:
                    img = MIMEImage(f.read())
                    img.add_header('Content-Disposition', 'attachment', 
                                 filename=os.path.basename(image_path))
                    msg.attach(img)
            
            # Send email
            with smtplib.SMTP(self.smtp_server, self.smtp_port) as server:
                server.starttls()
                server.login(self.smtp_username, self.smtp_password)
                server.send_message(msg)
            
            logger.info(f"Email sent to {to_emails}: {subject}")
            return True
            
        except Exception as e:
            logger.error(f"Failed to send email: {e}")
            return False
    
    def send_telegram(
        self,
        bot_token: str,
        chat_ids: List[str],
        message: str,
        image_path: Optional[str] = None
    ) -> bool:
        """
        Send Telegram notification
        
        Args:
            bot_token: Telegram bot token
            chat_ids: List of chat IDs
            message: Message text
            image_path: Optional path to image
            
        Returns:
            True if successful
        """
        try:
            import requests
            
            for chat_id in chat_ids:
                if image_path and os.path.exists(image_path):
                    # Send photo with caption
                    url = f"https://api.telegram.org/bot{bot_token}/sendPhoto"
                    with open(image_path, 'rb') as photo:
                        files = {'photo': photo}
                        data = {'chat_id': chat_id, 'caption': message}
                        response = requests.post(url, files=files, data=data)
                else:
                    # Send text message
                    url = f"https://api.telegram.org/bot{bot_token}/sendMessage"
                    data = {'chat_id': chat_id, 'text': message}
                    response = requests.post(url, data=data)
                
                if response.status_code == 200:
                    logger.info(f"Telegram message sent to {chat_id}")
                else:
                    logger.error(f"Telegram send failed: {response.text}")
                    return False
            
            return True
            
        except Exception as e:
            logger.error(f"Failed to send Telegram message: {e}")
            return False
    
    def send_sms(
        self,
        twilio_sid: str,
        twilio_token: str,
        from_number: str,
        to_numbers: List[str],
        message: str
    ) -> bool:
        """
        Send SMS notification using Twilio
        
        Args:
            twilio_sid: Twilio account SID
            twilio_token: Twilio auth token
            from_number: Twilio phone number
            to_numbers: List of recipient phone numbers
            message: SMS message text
            
        Returns:
            True if successful
        """
        try:
            from twilio.rest import Client
            
            client = Client(twilio_sid, twilio_token)
            
            for to_number in to_numbers:
                message_obj = client.messages.create(
                    body=message,
                    from_=from_number,
                    to=to_number
                )
                logger.info(f"SMS sent to {to_number}: {message_obj.sid}")
            
            return True
            
        except Exception as e:
            logger.error(f"Failed to send SMS: {e}")
            return False

    def check_cooldown(
        self,
        alert_key: str,
        cooldown_minutes: int = 15
    ) -> bool:
        """
        Check if enough time has passed since last alert

        Args:
            alert_key: Unique key for this alert type
            cooldown_minutes: Minimum minutes between alerts

        Returns:
            True if alert can be sent
        """
        from datetime import timedelta

        if alert_key not in self.last_alert_times:
            return True

        time_since_last = datetime.now() - self.last_alert_times[alert_key]
        cooldown = timedelta(minutes=cooldown_minutes)

        return time_since_last >= cooldown

    def update_alert_time(self, alert_key: str):
        """Update last alert time for a key"""
        self.last_alert_times[alert_key] = datetime.now()

    def send_left_behind_alert(
        self,
        object_info: Dict,
        camera_info: Dict,
        recipients: Dict,
        image_path: Optional[str] = None,
        cooldown_minutes: int = 15
    ) -> bool:
        """
        Send alert for left-behind object

        Args:
            object_info: Information about the object
            camera_info: Information about the camera
            recipients: Dict with 'email', 'telegram', 'sms' lists
            image_path: Path to snapshot image
            cooldown_minutes: Cooldown period

        Returns:
            True if alert sent successfully
        """
        alert_key = f"left_behind_{object_info['track_id']}"

        # Check cooldown
        if not self.check_cooldown(alert_key, cooldown_minutes):
            logger.info(f"Alert {alert_key} in cooldown period")
            return False

        # Prepare message
        subject = f"⚠️ Left-Behind Object Detected - {camera_info['name']}"

        body = f"""
        <html>
        <body>
            <h2 style="color: #ff6600;">Left-Behind Object Alert</h2>
            <p><strong>Camera:</strong> {camera_info['name']}</p>
            <p><strong>Location:</strong> {camera_info['location']}</p>
            <p><strong>Object Type:</strong> {object_info['class_name']}</p>
            <p><strong>First Detected:</strong> {object_info['first_seen']}</p>
            <p><strong>Stationary Since:</strong> {object_info['stationary_since']}</p>
            <p><strong>Time:</strong> {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}</p>
            <hr>
            <p>Please send security staff to collect and store this item.</p>
            <p><em>This is an automated alert from the School Security System.</em></p>
        </body>
        </html>
        """

        telegram_message = f"""
⚠️ LEFT-BEHIND OBJECT DETECTED

Camera: {camera_info['name']}
Location: {camera_info['location']}
Object: {object_info['class_name']}
First Seen: {object_info['first_seen']}
Stationary Since: {object_info['stationary_since']}

Please send security staff to collect this item.
        """

        sms_message = f"ALERT: {object_info['class_name']} left behind at {camera_info['name']}. Please collect."

        # Send notifications
        success = True

        if 'email' in recipients and recipients['email']:
            success &= self.send_email(
                recipients['email'],
                subject,
                body,
                image_path
            )

        if 'telegram' in recipients and recipients['telegram']:
            bot_token = os.getenv('TELEGRAM_BOT_TOKEN')
            if bot_token:
                success &= self.send_telegram(
                    bot_token,
                    recipients['telegram'],
                    telegram_message,
                    image_path
                )

        if 'sms' in recipients and recipients['sms']:
            twilio_sid = os.getenv('TWILIO_ACCOUNT_SID')
            twilio_token = os.getenv('TWILIO_AUTH_TOKEN')
            from_number = os.getenv('TWILIO_PHONE_NUMBER')

            if twilio_sid and twilio_token and from_number:
                success &= self.send_sms(
                    twilio_sid,
                    twilio_token,
                    from_number,
                    recipients['sms'],
                    sms_message
                )

        if success:
            self.update_alert_time(alert_key)

        return success

    def send_threat_alert(
        self,
        threat_info: Dict,
        camera_info: Dict,
        recipients: Dict,
        image_path: Optional[str] = None,
        cooldown_minutes: int = 5
    ) -> bool:
        """
        Send alert for detected threat

        Args:
            threat_info: Information about the threat
            camera_info: Information about the camera
            recipients: Dict with 'email', 'telegram', 'sms' lists
            image_path: Path to snapshot image
            cooldown_minutes: Cooldown period (shorter for threats)

        Returns:
            True if alert sent successfully
        """
        alert_key = f"threat_{camera_info['id']}_{datetime.now().strftime('%Y%m%d%H%M')}"

        # Check cooldown
        if not self.check_cooldown(alert_key, cooldown_minutes):
            logger.info(f"Alert {alert_key} in cooldown period")
            return False

        # Prepare message
        subject = f"🚨 URGENT: Threat Detected - {camera_info['name']}"

        body = f"""
        <html>
        <body>
            <h2 style="color: #ff0000;">⚠️ THREAT ALERT ⚠️</h2>
            <p><strong>Camera:</strong> {camera_info['name']}</p>
            <p><strong>Location:</strong> {camera_info['location']}</p>
            <p><strong>Threat Type:</strong> {threat_info['threat_type']}</p>
            <p><strong>Confidence:</strong> {threat_info['confidence']:.1%}</p>
            <p><strong>Time:</strong> {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}</p>
            <hr>
            <p style="color: red; font-weight: bold;">IMMEDIATE ACTION REQUIRED</p>
            <p>Please respond immediately to this incident.</p>
            <p><em>This is an automated alert from the School Security System.</em></p>
        </body>
        </html>
        """

        telegram_message = f"""
🚨 URGENT THREAT ALERT 🚨

Camera: {camera_info['name']}
Location: {camera_info['location']}
Threat: {threat_info['threat_type']}
Confidence: {threat_info['confidence']:.1%}
Time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

⚠️ IMMEDIATE ACTION REQUIRED ⚠️
        """

        sms_message = f"URGENT: {threat_info['threat_type']} detected at {camera_info['name']}. Respond immediately!"

        # Send notifications (all channels for threats)
        success = True

        if 'email' in recipients and recipients['email']:
            success &= self.send_email(
                recipients['email'],
                subject,
                body,
                image_path
            )

        if 'telegram' in recipients and recipients['telegram']:
            bot_token = os.getenv('TELEGRAM_BOT_TOKEN')
            if bot_token:
                success &= self.send_telegram(
                    bot_token,
                    recipients['telegram'],
                    telegram_message,
                    image_path
                )

        if 'sms' in recipients and recipients['sms']:
            twilio_sid = os.getenv('TWILIO_ACCOUNT_SID')
            twilio_token = os.getenv('TWILIO_AUTH_TOKEN')
            from_number = os.getenv('TWILIO_PHONE_NUMBER')

            if twilio_sid and twilio_token and from_number:
                success &= self.send_sms(
                    twilio_sid,
                    twilio_token,
                    from_number,
                    recipients['sms'],
                    sms_message
                )

        if success:
            self.update_alert_time(alert_key)

        return success


if __name__ == "__main__":
    # Example usage
    from dotenv import load_dotenv
    load_dotenv()

    alert_system = AlertSystem(
        smtp_server=os.getenv('SMTP_SERVER'),
        smtp_port=int(os.getenv('SMTP_PORT', 587)),
        smtp_username=os.getenv('SMTP_USERNAME'),
        smtp_password=os.getenv('SMTP_PASSWORD'),
        from_email=os.getenv('SMTP_USERNAME')
    )

    # Test left-behind object alert
    object_info = {
        'track_id': 1,
        'class_name': 'backpack',
        'first_seen': '2024-01-15 14:30:00',
        'stationary_since': '2024-01-15 14:35:00'
    }

    camera_info = {
        'id': 'CAM_001',
        'name': 'Classroom 1A',
        'location': 'Building A, Floor 1'
    }

    recipients = {
        'email': ['security@school.com']
    }

    alert_system.send_left_behind_alert(object_info, camera_info, recipients)


