import os
import hashlib
import shutil

def hash_file(filepath):
    """Calculate the SHA256 hash of a file."""
    hasher = hashlib.sha256()
    try:
        with open(filepath, 'rb') as f:
            buf = f.read()
            hasher.update(buf)
        return hasher.hexdigest()
    except Exception as e:
        print(f"Error hashing {filepath}: {e}")
        return None

def find_and_remove_duplicates(root_path):
    """Find and remove duplicate files in the given root path."""
    hash_dict = {}
    duplicates = []
    removed_files = []

    # Walk through the directory
    for dirpath, _, filenames in os.walk(root_path):
        for filename in filenames:
            # Only check PHP files
            if filename.endswith('.php'):
                filepath = os.path.join(dirpath, filename)
                
                # Calculate file hash
                file_hash = hash_file(filepath)
                
                if file_hash:
                    # Check if this hash already exists
                    if file_hash in hash_dict:
                        # Keep the original, remove the duplicate
                        duplicates.append((filepath, hash_dict[file_hash]))
                        try:
                            os.remove(filepath)
                            removed_files.append(filepath)
                            print(f"Removed duplicate: {filepath}")
                        except Exception as e:
                            print(f"Error removing {filepath}: {e}")
                    else:
                        hash_dict[file_hash] = filepath

    return duplicates, removed_files

# Find and remove duplicates in the project directory
root_path = r'c:\xampp\htdocs\apsdreamhomefinal'
duplicate_files, removed_files = find_and_remove_duplicates(root_path)

# Print summary
print("\nDuplicate Removal Summary:")
if not duplicate_files:
    print("No duplicate PHP files found.")
else:
    print(f"Total duplicate files removed: {len(removed_files)}")
    print("\nRemoved Files:")
    for removed in removed_files:
        print(removed)
    print("\nOriginal Files Kept:")
    for dup in duplicate_files:
        print(dup[1])
