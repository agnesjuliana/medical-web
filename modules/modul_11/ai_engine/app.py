import os
import cv2
import numpy as np
import tensorflow as tf
from flask import Flask, request, jsonify
from flask_cors import CORS

from config import cfg
from network.model import Network
from utils import rescale_input, craniofacial_landmark_regions, decode_cephalometric_landmarks

app = Flask(__name__)
CORS(app)

print("\n" + "="*50)
print("[INFO] Sedang memuat Arsitektur CEPHMark-Net AI...")
print("="*50 + "\n")

network = Network(
    backbone_name="resnet50",
    freeze_backbone=False,
    backbone_weights=None
)

# Mengecek apakah Anda sudah berhasil menjalankan proses Training sebelumnya
best_weights_path = "./models/cephmark_final_weights_epoch2.h5"
if os.path.exists(best_weights_path):
    print(f"\n[INFO] 🧠 Disket Otak AI telah ditemukan! Menginstall memori dari: {best_weights_path}...")
    network.model.load_weights(best_weights_path)
else:
    print(f"\n[WARNING] Belum ada otak / model training di {best_weights_path}!")
    print("[WARNING] Server API meluncur dengan mode 'AI Baru Lahir' (Tebakan Random)!")

# Call a dummy prediction request to initialize the TensorFlow graphical trace properly.
# Without this, the first request might take excessively long or hang.
dummy_img_tensor = tf.zeros((1, cfg.HEIGHT, cfg.WIDTH, 3), dtype=tf.float32)
dummy_pred_landmarks = network.landmark_detection_module(inputs=dummy_img_tensor, training=False)
dummy_pred_landmarks = tf.stack([dummy_pred_landmarks[:, 0::2], dummy_pred_landmarks[:, 1::2]], axis=-1)
dummy_proposals = tf.zeros((3, 1, cfg.NUM_LANDMARKS, 4), dtype=tf.float32)

for index in range(cfg.NUM_LANDMARKS):
    _ = network.landmark_refinement_module.heads[index](
        inputs=[dummy_img_tensor, dummy_proposals[:, :, index, :]], 
        training=False
    )

print("\n[INFO] AI Server Backend siap menerima koneksi (Port 5000)!")

@app.route('/predict', methods=['POST'])
def predict():
    if 'image' not in request.files:
        return jsonify({"error": "No image uploaded in form-data under key 'image'"}), 400

    file = request.files['image']
    filestr = file.read()
    
    # 1. Decode Image from Stream
    npimg = np.frombuffer(filestr, np.uint8)
    image = cv2.imdecode(npimg, cv2.IMREAD_COLOR)
    image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    
    # Keep original dimensions for response mapping later
    orig_height, orig_width = image.shape[0], image.shape[1]

    # 2. Resize to CEPHMark Dimensions (640x800)
    resized_image = cv2.resize(image, dsize=(cfg.WIDTH, cfg.HEIGHT), interpolation=cv2.INTER_CUBIC)
    
    # 3. Model Expected Tensor
    input_tensor = np.expand_dims(resized_image, axis=0)
    input_tensor = rescale_input(input_tensor, scale=(1.0 / 255.0))
    
    print("[INFO] Request Diterima: Melakukan inferensi CPU pada gambar... (harap bersabar)")
    
    # 4. Stage-1 Landmark Detection
    pred_landmarks = network.landmark_detection_module(inputs=input_tensor, training=False)
    pred_landmarks = tf.stack([pred_landmarks[:, 0::2], pred_landmarks[:, 1::2]], axis=-1)

    # 5. Extract Proposals
    image_height, image_width = cfg.HEIGHT, cfg.WIDTH
    block3_proposals = craniofacial_landmark_regions(pred_landmarks, height=(image_height / 8),  width=(image_width / 4),  size=7)
    block4_proposals = craniofacial_landmark_regions(pred_landmarks, height=(image_height / 16), width=(image_width / 8),  size=5)
    block5_proposals = craniofacial_landmark_regions(pred_landmarks, height=(image_height / 32), width=(image_width / 16), size=3)
    proposals = tf.stack([block3_proposals, block4_proposals, block5_proposals])

    # 6. Stage-2 Landmark Refinement (29 paralel heads)
    pred_locations = []
    for index in range(cfg.NUM_LANDMARKS):
        candidate_regions = proposals[:, :, index, :]
        refine_locations = network.landmark_refinement_module.heads[index](
            inputs=[input_tensor, candidate_regions], 
            training=False
        ) + pred_landmarks[:, index, :]
        pred_locations.append(refine_locations)
        
    pred_locations = tf.stack(pred_locations, axis=1)

    # 7. Reverse Mapping / Decode Coordinate into ORIGINAL UPLOADED RESOLUTION 
    final_landmarks = decode_cephalometric_landmarks(pred_locations, height=orig_height, width=orig_width)
    final_landmarks = final_landmarks.numpy()[0] # get out of batch dimension (shape: 29, 2)
    
    # 8. Formatting output for the Frontend Array format
    results = []
    for idx in range(cfg.NUM_LANDMARKS):
        x = float(final_landmarks[idx, 0])
        y = float(final_landmarks[idx, 1])
        label = cfg.ANATOMICAL_LANDMARKS[str(idx)]
        results.append({
            "id": idx,
            "label": label,
            "x": x,
            "y": y
        })

    print("[SUCCESS] Inferensi Selesai! Koordinat dikirimkan ke Website.")
    
    return jsonify({
        "success": True,
        "landmarks": results,
        "message": "Berhasil memprediksi 29 landmarks (Untrained weights simulate)"
    })

if __name__ == '__main__':
    # Pastikan berjalan di thread utama untuk stabilitas Graph Tensorflow
    app.run(host='0.0.0.0', port=5000, debug=False, threaded=False)
