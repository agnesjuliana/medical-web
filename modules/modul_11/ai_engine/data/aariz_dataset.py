import json
import numpy as np
import random
import cv2
import os

from config import cfg

class AarizDataset(object):

    def __init__(
        self,
        dataset_folder_path: str,
        mode: str
    ) -> None:

        if mode in ["train", "valid", "test"]:
            self.mode = mode
        else:
            raise ValueError("mode could only be train, valid or test")

        self.images_root_path = os.path.join(dataset_folder_path, self.mode, "Cephalograms")

        self.annotations_root_path = os.path.join(dataset_folder_path, self.mode, "Annotations", "Cephalometric Landmarks")
        self.senior_annotations_root = os.path.join(self.annotations_root_path, "Senior Orthodontists")
        self.junior_annotations_root = os.path.join(self.annotations_root_path, "Junior Orthodontists")

        self.images_list = list(sorted(os.listdir(self.images_root_path)))
        
        self.landmarkToIndex = {name: int(idx) for idx, name in cfg.ANATOMICAL_LANDMARKS.items()}

    def __getitem__(self, index: int):
        image_file_name = self.images_list[index]
        label_file_name = self.images_list[index].split(".")[0] + "." + "json"

        image = self.get_image(image_file_name)
        label = self.get_label(label_file_name)

        return image, label

    def get_image(self, file_name: str):
        file_path = os.path.join(self.images_root_path, file_name)

        image = cv2.imread(file_path)
        image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)

        return image
        
    def _read_json_landmarks(self, file_path):
        with open(file_path, 'r') as file:
            data = json.load(file)
        
        landmarks = np.zeros(shape=(cfg.NUM_LANDMARKS, 2), dtype=np.float32)
        for lm in data.get("landmarks", []):
            title = lm["title"]
            if title in self.landmarkToIndex:
                idx = self.landmarkToIndex[title]
                landmarks[idx, 0] = lm["value"]["x"]
                landmarks[idx, 1] = lm["value"]["y"]
        return landmarks

    def get_label(self, file_name: str) -> np.ndarray:
        senior_path = os.path.join(self.senior_annotations_root, file_name)
        senior_ann = self._read_json_landmarks(senior_path) if os.path.exists(senior_path) else None

        junior_path = os.path.join(self.junior_annotations_root, file_name)
        junior_ann = self._read_json_landmarks(junior_path) if os.path.exists(junior_path) else None

        if senior_ann is not None and junior_ann is not None:
            landmarks = np.ceil((0.5) * (junior_ann + senior_ann))
        elif senior_ann is not None:
            landmarks = senior_ann
        elif junior_ann is not None:
            landmarks = junior_ann
        else:
            landmarks = np.zeros(shape=(cfg.NUM_LANDMARKS, 2), dtype=np.float32)

        return np.array(landmarks, dtype=np.float32)

    def shuffle(self):
        random.shuffle(self.images_list)

    def __len__(self):
        return len(self.images_list)
