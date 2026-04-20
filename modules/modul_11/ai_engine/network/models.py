import tensorflow as tf

class Backbone(object):
    def __init__(self, name, inputs, weights_root_path=None):
        self.name = name
        weights = "imagenet" if weights_root_path is None else weights_root_path
        
        if name == "resnet50":
            self.network = tf.keras.applications.ResNet50(
                include_top=False, 
                input_tensor=inputs, 
                weights=weights
            )
        elif name == "vgg16":
            self.network = tf.keras.applications.VGG16(
                include_top=False, 
                input_tensor=inputs, 
                weights=weights
            )
        elif name == "vgg19":
            self.network = tf.keras.applications.VGG19(
                include_top=False, 
                input_tensor=inputs, 
                weights=weights
            )
        else:
            raise ValueError(f"Backbone {name} is not supported fallback yet.")

    def freeze(self):
        for layer in self.network.layers:
            layer.trainable = False
