from flask import Flask, request, jsonify
from flask_cors import CORS
import mysql.connector
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

app = Flask(__name__)
CORS(app)

def load_hotels():
    conn = mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='bayatni_db'
    )
    cursor = conn.cursor()
    cursor.execute("SELECT id, title, location, price, rating, reviews_count, region, image_url, features FROM hotels")
    hotels = cursor.fetchall()
    conn.close()
    return hotels

hotels_data = load_hotels()

corpus = [f"{title} {location} {region} {features}" for (_, title, location, price, rating, reviews_count, region, image_url, features) in hotels_data]
vectorizer = TfidfVectorizer()
X = vectorizer.fit_transform(corpus)

@app.route('/search', methods=['GET'])

def search():
    query = request.args.get('q', '')
    if not query:
        return jsonify([])

    query_vec = vectorizer.transform([query])
    scores = cosine_similarity(query_vec, X)[0]
    ranked_indices = scores.argsort()[::-1]

    results = []
    for idx in ranked_indices[:10]:  # Limit to top 10 results
        hotel = hotels_data[idx]
        score = float(scores[idx])
        if score > 0:
            results.append({
                "id": hotel[0],
                "title": hotel[1],
                "location": hotel[2],
                "price": hotel[3],
                "rating": hotel[4],
                "reviews_count": hotel[5],
                "region": hotel[6],
                "image_url": hotel[7],
                "features": hotel[8],
                "score": round(score, 3)
            })

    return jsonify(results)

if __name__ == '__main__':
    app.run(port=5000)
