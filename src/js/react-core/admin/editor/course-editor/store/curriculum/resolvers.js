import {fetchCurriculumData} from "./actions";

export function* getCurriculumData(postId) {
    yield fetchCurriculumData(postId);
}