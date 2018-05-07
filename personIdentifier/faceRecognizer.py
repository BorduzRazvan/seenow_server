import sys

sys.path.append('/usr/local/lib/python3.6/site-packages')
import cv2
import os
import numpy as np
subjects = []
cascFisher = "haarcascade_frontalface_default.xml"
cascLBPH = "lbpcascade_frontalface.xml"
width_d, height_d = 500, 500
def faceDetection_LBPH(img):
	gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
	# Create the haar cascade
	faceCascade = cv2.CascadeClassifier(cascLBPH)
	faces = faceCascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5,minSize=(30, 30))
	#if no faces are detected
	if(len(faces) == 0):
		return None, None
	#Consider that in an image is only one face, extract the face area
	(x, y, w, h) = faces[0]
	out = cv2.resize(gray[y:y+w, x:x+h], None, fx = 0.5, fy=0.5)
	return out, faces[0]

def faceDetection_Fisher(img):
	gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
	# Create the haar cascade
	faceCascade = cv2.CascadeClassifier(cascFisher)
	faces = faceCascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5,minSize=(30, 30))
	#if no faces are detected
	if(len(faces) == 0):
		return None, None
	#Consider that in an image is only one face, extract the face area
	(x, y, w, h) = faces[0]
	out = cv2.resize(gray[y:y+w, x:x+h], (width_d, height_d))
	return out, faces[0]


def prepare_trainigData_LBPH(data_folder_path):
	dirs = os.listdir(data_folder_path)
	faces = [] # list for all faces
	labels = [] # list for labels of faces
	for dir_name in dirs:
		#consider folders starting with userID_xyz where xyz is the userId
		if not dir_name.startswith("userID_"):
			continue;
		#if we remove the userID_ from foldername we'll get the label
		item = dir_name.replace("userID_","")
		label = int(item)
		print("Sunt aici: "+item)
		if item not in subjects:
			subjects.append(item)
		dir_path = data_folder_path + "/" + dir_name
		images_names = os.listdir(dir_path)

		for image_name in images_names:
			#ignore system files
			if image_name.startswith("."):
				continue;

			image_path = dir_path + "/" + image_name
			image = cv2.imread(image_path)
			face, rect = faceDetection_LBPH(image)
			if face is not None:
				faces.append(face)
				labels.append(label)
			else:
				print("No face detected: "+image_path)
	return faces, labels

def prepare_trainigData_Fisher(data_folder_path):
	print("Sunt aici!!!!!")
	dirs = os.listdir(data_folder_path)
	faces = [] # list for all faces
	labels = [] # list for labels of faces
	for dir_name in dirs:
		#consider folders starting with userID_xyz where xyz is the userId
		if not dir_name.startswith("userID_"):
			continue;
		#if we remove the userID_ from foldername we'll get the label
		item = dir_name.replace("userID_","")
		label = int(item)
		print("Sunt aici: "+item)
		if item not in subjects:
			subjects.append(item)
		dir_path = data_folder_path + "/" + dir_name
		images_names = os.listdir(dir_path)

		for image_name in images_names:
			#ignore system files
			if image_name.startswith("."):
				continue;

			image_path = dir_path + "/" + image_name
			image = cv2.imread(image_path)
			face, rect = faceDetection_Fisher(image)
			if face is not None:
				faces.append(face)
				labels.append(label)
			else:
				print("No face detected: "+image_path)
	return faces, labels

def predictLBPH(predict_img):
	img = predict_img.copy()
	face, rect = faceDetection_LBPH(img)
	if(face is not None):
		label, confidence = face_recognizer1.predict(face)
		return subjects[label], confidence

def predictFisher(predict_img):
	img = predict_img.copy()
	face, rect = faceDetection_Fisher(img)
	if(face is not None):
		label, confidence = face_recognizer2.predict(face)
		return subjects[label], confidence

# Get user supplied values
imagePath = sys.argv[1]
allImagesPathDir = sys.argv[2]
trainBool = sys.argv[3]
trainBool = int(trainBool)
face_recognizer1 = cv2.face.LBPHFaceRecognizer_create()
face_recognizer2 = cv2.face.EigenFaceRecognizer_create()
if(trainBool == 1):
	print ("Sunt aici")
	subjects = [""]
	faces, labels = prepare_trainigData_LBPH(allImagesPathDir)
	face_recognizer1.train(faces, np.array(labels))
	face_recognizer1.write('trainer/trainer_LBPH.yml')
	faces, labels = prepare_trainigData_Fisher(allImagesPathDir)
	print("Am ajuns aici")
	face_recognizer2.train(faces, np.array(labels))
	face_recognizer2.write('trainer/trainer_Fisher.yml')
	thefile = open('trainer/subjects.txt', 'w')
	for item in subjects:
  		thefile.write("%s\n" % item)
else:
	with open("trainer/subjects.txt") as file:
		for line in file:
			line = line.strip()
			subjects.append(line)
	face_recognizer1.read('trainer/trainer_LBPH.yml')
	face_recognizer2.read('trainer/trainer_Fisher.yml')

#	faces, labels = prepare_trainigData(allImagesPathDir)
#	face_recognizer.train(faces, np.array(labels))
	toPredictImage = cv2.imread(imagePath)
	predictedLabel_LBPH,confidence_LBPH = predictLBPH(toPredictImage)
	predictedLabel_Fisher,confidence_Fisher = predictFisher(toPredictImage)
	if(confidence_LBPH < 60 and confidence_Fisher < 1600 and predictedLabel_LBPH == predictedLabel_Fisher):
		print ("Found id:"+predictedLabel_LBPH+" and confidence_LBPH: "+str(confidence_LBPH)+" and confidence_Fisher: "+str(confidence_Fisher))
	else:
		print ("Not recognized")
