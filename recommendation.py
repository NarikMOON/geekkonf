import numpy as np
import pandas as pd
import mysql.connector as connection
from sklearn.model_selection import cross_validate as cv
from sklearn.model_selection import train_test_split as tts
from sklearn.metrics import mean_squared_error as mse
from math import sqrt
import sys, json 
needuid = int(sys.argv[1])
mydb = connection.connect(host="localhost", database = 'geekkonfclubdatabase',user="root", passwd="mysql",use_pure=True)
marks = pd.read_sql('select * from marks',mydb)
n = len(marks['id'].unique())
marks_sample = marks[:n]
n_users = len(marks_sample['uid'].unique())
n_movies = len(marks_sample['mid'].unique())
uids = marks_sample['uid'].unique()
mids = marks_sample['mid'].unique()
def scale_mids(mid):
    scaled = np.where(mids == mid)[0][0] + 1
    return scaled
def scale_uid(uid):
    scaled = np.where(uids == uid)[0][0] + 1
    return scaled
nnu=scale_uid(needuid)
RDS = marks_sample['mid'].unique()
marks_sample['mid'] = marks_sample['mid'].apply(scale_mids)
marks_sample['uid'] = marks_sample['uid'].apply(scale_uid)
train_data, test_data = tts(marks_sample, test_size=0.2)
train_data = marks_sample
def rmse(prediction, ground_truth):
    # Оставим оценки, предсказанные алгоритмом, только для соотвествующего набора данных
    prediction = np.nan_to_num(prediction)[ground_truth.nonzero()].flatten()
    # Оставим оценки, которые реально поставил пользователь, только для соотвествующего набора данных
    ground_truth = np.nan_to_num(ground_truth)[ground_truth.nonzero()].flatten()    
    mse1 = mse(prediction, ground_truth)
    return sqrt(mse1)
train_data_matrix = np.zeros((n_users, n_movies))
for line in train_data.itertuples():
    train_data_matrix[line[2] - 1, line[3] - 1] = line[4]    
test_data_matrix = np.zeros((n_users, n_movies))
for line in test_data.itertuples():
    test_data_matrix[line[2] - 1, line[3] - 1] = line[4]
from  sklearn.metrics.pairwise import pairwise_distances
# считаем косинусное расстояние для пользователей и фильмов 
# (построчно и поколоночно соотвественно).
user_similarity = pairwise_distances(train_data_matrix, metric='cosine')
media_similarity = pairwise_distances(train_data_matrix.T, metric='cosine')
def k_fract_mean_predict(top):
    top_similar = np.zeros((n_users, top))    
    for i in range(n_users):
        user_sim = user_similarity[i]
        top_sim_users = user_sim.argsort()[1:top + 1]
        for j in range(top):
            top_similar[i, j] = top_sim_users[j]            
    abs_sim = np.abs(user_similarity)
    pred = np.zeros((n_users, n_movies))    
    for i in range(n_users):
        indexes = top_similar[i].astype(int)
        numerator = user_similarity[i][indexes]        
        mean_rating = np.array([x for x in train_data_matrix[i] if x > 0]).mean()
        diff_ratings = train_data_matrix[indexes] - train_data_matrix[indexes].mean()
        numerator = numerator.dot(diff_ratings)
        denominator = abs_sim[i][top_similar[i].astype(int)].sum()        
        pred[i] = mean_rating + numerator / denominator        
    return pred

k_predict = k_fract_mean_predict(2)
for i in range (n_movies):
    if (k_predict[nnu-1][i]>4):
        print (RDS[i])
#print ('Имеющиеся оценки ',train_data_matrix)
#print ('Предположительные оценки ',k_predict)
#print('Среднее квадратическое отклонение ', rmse(k_predict, test_data_matrix))