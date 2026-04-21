from tensorflow.keras.layers import Input
from easydict import EasyDict as edict
import tensorflow as tf

config = edict()

# original height and width of image
config.ORIGINAL_HEIGHT = 2400
config.ORIGINAL_WIDTH = 1935

# height and width to resize image (Harus merupakan kelipatan absolut 32 untuk pyramid network Tensorflow)
config.HEIGHT = 128
config.WIDTH  = 96

# input cephalogram image to the base network
config.IMAGE_INPUT = Input(shape=(config.HEIGHT, config.WIDTH, 3), name="cephalogram")

# landmark region proposals (LRPs) input to the landmark detection network
config.PROPOSALS_INPUT = Input(shape=(None, 4), name="landmark_region_proposals")

# Image resolution (mm/pixel)
config.IMAGE_RESOLUTION = 0.1

# cephalometric landmarks
config.ANATOMICAL_LANDMARKS = {
    "0": "A-point",
    "1": "Anterior Nasal Spine",
    "2": "B-point",
    "3": "Menton",
    "4": "Nasion",
    "5": "Orbitale",
    "6": "Pogonion",
    "7": "Posterior Nasal Spine",
    "8": "Pronasale",
    "9": "Ramus",
    "10": "Sella",
    "11": "Articulare",
    "12": "Condylion",
    "13": "Gnathion",
    "14": "Gonion",
    "15": "Porion",
    "16": "Lower 2nd PM Cusp Tip",
    "17": "Lower Incisor Tip",
    "18": "Lower Molar Cusp Tip",
    "19": "Upper 2nd PM Cusp Tip",
    "20": "Upper Incisor Apex",
    "21": "Upper Incisor Tip",
    "22": "Upper Molar Cusp Tip",
    "23": "Lower Incisor Apex",
    "24": "Labrale inferius",
    "25": "Labrale superius",
    "26": "Soft Tissue Nasion",
    "27": "Soft Tissue Pogonion",
    "28": "Subnasale",
}

# number of cephalometric landmarks
config.NUM_LANDMARKS = 19


config.BACKBONE_BLOCKS_INFO = {
    "vgg16": {
        "C1": "block1_conv2",
        "C2": "block2_conv2",
        "C3": "block3_conv3",
        "C4": "block4_conv3",
        "C5": "block5_conv3"
    },
    "vgg19": {
        "C1": "block1_conv2",
        "C2": "block2_conv2",
        "C3": "block3_conv4",
        "C4": "block4_conv4",
        "C5": "block5_conv4"
    },
    "darknet19": {
        "C1": "block1_conv1",
        "C2": "block2_conv1",
        "C3": "block3_conv3",
        "C4": "block4_conv3",
        "C5": "block5_conv5",
        "C6": "block6_conv5",
    },
    "darknet53": {
        "C1": "block1.1_out",
        "C2": "block2.2_out",
        "C3": "block3.8_out",
        "C4": "block4.8_out",
        "C5": "block5.4_out"
    },
    "resnet18": {
        "C2": "block2.2_out",
        "C3": "block3.2_out",
        "C4": "block4.2_out",
        "C5": "block5.2_out"
    },
    "resnet34": {
        "C2": "block2.3_out",
        "C3": "block3.4_out",
        "C4": "block4.6_out",
        "C5": "block5.3_out"
    },
    "resnet50": {
        "C2": "conv2_block3_out",
        "C3": "conv3_block4_out",
        "C4": "conv4_block6_out",
        "C5": "conv5_block3_out"
    }
}

# Region of interest pool size
config.ROI_POOL_SIZE = (5, 5)

# margin (in pixels) at each side of lateral skull face
config.BOX_MARGIN = 32

config.TRAIN = edict()
# number of epochs
config.TRAIN.EPOCHS = 10
# optimizer
config.TRAIN.OPTIMIZER = tf.keras.optimizers.Adam(learning_rate=0.0001)

cfg = config
