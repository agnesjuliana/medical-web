from config import cfg
import numpy as np
import random
import cv2
import os

class Cepha400Dataset(object):
    def __init__(self, dataset_folder_path: str, mode: str) -> None:
        if mode not in ["train", "valid", "test"]:
             raise ValueError("mode could only be train, valid or test")
        self.mode = mode
        
        # Kaggle zip unzips to datasets/cepha400/cepha400/ -> containing the 400 images
        self.images_root_path = os.path.join(dataset_folder_path, "cepha400")
        
        # Load CSV based on mode (Kaggle split: train=150, valid/test1=150, test2=100)
        if mode == "train":
            csv_path = os.path.join(dataset_folder_path, "train_senior.csv")
        elif mode == "valid":
            csv_path = os.path.join(dataset_folder_path, "test1_senior.csv")
        else: # test
            csv_path = os.path.join(dataset_folder_path, "test2_senior.csv")
            
        self.data_records = []
        with open(csv_path, 'r') as f:
            lines = f.readlines()
            for line in lines[1:]: # skip header
                parts = line.strip().split(',')
                if len(parts) >= 39:
                    img_name = parts[0]
                    coords = []
                    # There are 19 landmarks * 2 (x,y) = 38 columns
                    for i in range(1, 39, 2):
                        coords.append([float(parts[i]), float(parts[i+1])])
                    self.data_records.append({'image': img_name, 'landmarks': coords})
                    
    def __getitem__(self, index: int):
        record = self.data_records[index]
        image_path = os.path.join(self.images_root_path, record['image'])
        
        image = cv2.imread(image_path)
        image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        
        landmarks = np.array(record['landmarks'][:cfg.NUM_LANDMARKS], dtype=np.float32)
        return image, landmarks

    def shuffle(self):
        random.shuffle(self.data_records)

    def __len__(self):
        return len(self.data_records)
