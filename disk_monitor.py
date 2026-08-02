
import psutil

def check_disk_usage():
    # Get current disk usage
    usage = psutil.disk_usage('/')

    # Calculate percentage used
    percent_used = usage.percent

    # Set threshold (90%)
    threshold = 90

    # Check if usage exceeds threshold
    if percent_used > threshold:
        print(f"Disk usage alert! Current usage: {percent_used}%")

    else:
        print(f"Disk usage is within limits: {percent_used}%")

# Run the function
check_disk_usage()
